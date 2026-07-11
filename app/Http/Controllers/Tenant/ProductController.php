<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Product::class);

        $tenant     = app(TenantContext::class)->get();
        $products   = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.index', compact('tenant', 'products', 'categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        $tenant     = app(TenantContext::class)->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.form', compact('tenant', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:200'],
            'image'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        Product::create($data);

        return redirect()
            ->route('tenant.products.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'product-created');
    }

    public function edit(Request $request): View
    {
        $product = Product::findOrFail((int) $request->route('product'));
        Gate::authorize('update', $product);

        $tenant     = app(TenantContext::class)->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.form', compact('tenant', 'product', 'categories'));
    }

    public function update(Request $request): RedirectResponse
    {
        $product = Product::findOrFail((int) $request->route('product'));
        Gate::authorize('update', $product);

        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:200'],
            'image'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product->update($data);

        return redirect()
            ->route('tenant.products.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'product-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $product = Product::findOrFail((int) $request->route('product'));
        Gate::authorize('delete', $product);

        $tenant = app(TenantContext::class)->get();

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return redirect()
            ->route('tenant.products.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'product-deleted');
    }
}
