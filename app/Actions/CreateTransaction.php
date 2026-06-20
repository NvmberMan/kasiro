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
    public function handle(User $cashier, array $items, float $paid, string $paymentMethod = 'cash'): Transaction
    {
        if (empty($items)) {
            throw new RuntimeException('Keranjang tidak boleh kosong.');
        }

        return DB::transaction(function () use ($cashier, $items, $paid, $paymentMethod): Transaction {
            $productIds = array_column($items, 'product_id');

            // Lock rows to prevent concurrent stock depletion (E11)
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
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

                $subtotal    = $product->price * $qty;
                $total      += $subtotal;
                $lineItems[] = [
                    'product'  => $product,
                    'qty'      => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $transaction = Transaction::create([
                'cashier_id'     => $cashier->id,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => max(0, $paid - $total),
                'payment_method' => $paymentMethod,
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
