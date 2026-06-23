<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PreviewController extends Controller
{
    /**
     * Public, token-guarded render of the real cashier (POS) screen used only by
     * the screenshot job. The token is minted server-side per render request, so
     * this never exposes tenant data publicly, yet needs no interactive login —
     * the tenant belongs to the owner anyway. We render exactly what the owner
     * would see by binding the owner to the request for its lifetime only.
     */
    public function index(Request $request, TenantContext $context): View
    {
        $tenant = $context->get();

        $expected = Cache::get(self::cacheKey($tenant->id));
        abort_unless(
            is_string($expected) && hash_equals($expected, (string) $request->query('token')),
            403
        );

        // Bind the owner for this request only (no session login), so the real
        // layout/nav render with the owner's role exactly as they'd see it.
        if ($tenant->owner) {
            Auth::setUser($tenant->owner);
        }

        $products   = Product::with('category')->active()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.pos.index', compact('tenant', 'products', 'categories'));
    }

    public static function cacheKey(int $tenantId): string
    {
        return "tenant-preview-token:{$tenantId}";
    }
}
