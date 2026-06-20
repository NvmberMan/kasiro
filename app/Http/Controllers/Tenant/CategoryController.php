<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        $tenant     = app(TenantContext::class)->get();
        $categories = Category::orderBy('name')->get();

        return view('tenant.categories.index', compact('tenant', 'categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        $tenant = app(TenantContext::class)->get();

        return view('tenant.categories.form', compact('tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->where('tenant_id', $tenant->id),
            ],
        ]);

        Category::create(['name' => $request->input('name')]);

        return redirect()
            ->route('tenant.categories.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'category-created');
    }

    public function edit(Request $request): View
    {
        $category = Category::findOrFail((int) $request->route('category'));
        Gate::authorize('update', $category);

        $tenant = app(TenantContext::class)->get();

        return view('tenant.categories.form', compact('tenant', 'category'));
    }

    public function update(Request $request): RedirectResponse
    {
        $category = Category::findOrFail((int) $request->route('category'));
        Gate::authorize('update', $category);

        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->where('tenant_id', $tenant->id)->ignore($category->id),
            ],
        ]);

        $category->update(['name' => $request->input('name')]);

        return redirect()
            ->route('tenant.categories.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'category-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $category = Category::findOrFail((int) $request->route('category'));
        Gate::authorize('delete', $category);

        $tenant = app(TenantContext::class)->get();

        $category->delete();

        return redirect()
            ->route('tenant.categories.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'category-deleted');
    }
}
