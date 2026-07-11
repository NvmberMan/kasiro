<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateTransaction
{
    /**
     * @param array<int, array{product_id: int, qty: int}> $items
     */
    public function handle(User $cashier, array $items, float $paid): Transaction
    {
        if (empty($items)) {
            throw new RuntimeException(__('Keranjang tidak boleh kosong.'));
        }

        return DB::transaction(function () use ($cashier, $items, $paid): Transaction {
            $productIds = array_column($items, 'product_id');

            // Lock rows to prevent concurrent stock depletion (E11)
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id'])
                    ?? throw new RuntimeException("Produk #{$item['product_id']} tidak ditemukan.");

                $qty = (int) $item['qty'];

                if ($qty < 1) {
                    throw new RuntimeException("Jumlah produk \"{$product->name}\" tidak valid.");
                }

                if ($product->stock < $qty) {
                    throw new RuntimeException("Stok \"{$product->name}\" tidak cukup (tersisa {$product->stock}).");
                }

                $lineSubtotal = $product->price * $qty;
                $subtotal    += $lineSubtotal;
                $lineItems[]  = [
                    'product'  => $product,
                    'qty'      => $qty,
                    'subtotal' => $lineSubtotal,
                ];
            }

            // Tax is a per-store percentage applied on top of the subtotal.
            $taxPercent = (float) (app(\App\Support\TenantContext::class)->get()?->taxPercent() ?? 0);
            $tax        = round($subtotal * $taxPercent / 100);
            $total      = $subtotal + $tax;

            $transaction = Transaction::create([
                'cashier_id'     => $cashier->id,
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => max(0, $paid - $total),
                'transacted_at'  => now(),
            ]);

            foreach ($lineItems as $line) {
                $transaction->items()->create([
                    'product_id' => $line['product']->id,
                    'qty'        => $line['qty'],
                    'unit_price' => $line['product']->price,
                    'subtotal'   => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['qty']);
            }

            return $transaction;
        });
    }
}
