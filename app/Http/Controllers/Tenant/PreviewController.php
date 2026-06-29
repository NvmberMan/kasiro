<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
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

        $products = Product::with('category')->active()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.pos.index', compact('tenant', 'products', 'categories'));
    }

    /**
     * Token-guarded render of the POS screen styled with a *template's* config,
     * used only by the template screenshot job. The subdomain in the URL resolves
     * a "donor" tenant (via ResolveTenant) that supplies a populated catalog, then
     * we overlay the template's branding (layout/theme/palette) in-memory so the
     * screenshot reflects the template rather than the donor's own customisations.
     */
    public function template(Request $request, TenantContext $context): View
    {
        // Read the {template} segment explicitly: this domain group runs no
        // implicit route-model binding, and positional argument binding would
        // collide with the {subdomain} route parameter.
        $template = Template::findOrFail($request->route('template'));

        $expected = Cache::get(self::templateCacheKey($template->id));
        abort_unless(
            is_string($expected) && hash_equals($expected, (string) $request->query('token')),
            403
        );

        $tenant = $context->get();

        // Overlay the template look without persisting — the donor tenant keeps
        // its real data for product scoping, but renders with template branding.
        $tenant->setAttribute('theme_config', $template->default_config);
        $tenant->setAttribute('name', $template->name);

        if ($tenant->owner) {
            Auth::setUser($tenant->owner);
        }

        $products = Product::with('category')->active()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.pos.index', compact('tenant', 'products', 'categories'));
    }

    public static function cacheKey(int $tenantId): string
    {
        return "tenant-preview-token:{$tenantId}";
    }

    public static function templateCacheKey(int $templateId): string
    {
        return "template-preview-token:{$templateId}";
    }
}
