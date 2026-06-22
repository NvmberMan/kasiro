<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateTransaction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PosController extends Controller
{
    public function index(): View
    {
        $tenant     = app(TenantContext::class)->get();
        $products   = Product::with('category')->active()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.pos.index', compact('tenant', 'products', 'categories'));
    }

    public function checkout(Request $request, CreateTransaction $action): RedirectResponse
    {
        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'cart' => ['required', 'json'],
            'paid' => ['required', 'numeric', 'min:0'],
        ]);

        $items = json_decode($request->input('cart'), true);

        if (empty($items)) {
            return back()->withErrors(['cart' => 'Keranjang tidak boleh kosong.']);
        }

        try {
            $transaction = $action->handle(
                cashier: $request->user(),
                items: $items,
                paid: (float) $request->input('paid'),
            );

            return redirect()
                ->route('tenant.pos', ['subdomain' => $tenant->subdomain])
                ->with('transaction_id', $transaction->id)
                ->with('checkout_total', (float) $transaction->total)
                ->with('checkout_paid', (float) $transaction->paid)
                ->with('checkout_change', (float) $transaction->change)
                ->with('status', 'checkout-success');
        } catch (RuntimeException $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }
}
