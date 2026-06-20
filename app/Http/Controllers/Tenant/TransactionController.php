<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Transaction::class);

        $tenant       = app(TenantContext::class)->get();
        $transactions = Transaction::with(['cashier', 'items.product'])
            ->latest('transacted_at')
            ->paginate(20);

        return view('tenant.transactions.index', compact('tenant', 'transactions'));
    }
}
