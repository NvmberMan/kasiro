<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\UpdateTenantSettings;
use App\Http\Controllers\Controller;
use App\Rules\ValidSubdomain;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manage-tenant-settings');

        $tenant   = app(TenantContext::class)->get();
        $palettes = config('branding.palettes');
        $layouts  = config('branding.layouts');
        $themes   = config('branding.themes');

        return view('tenant.settings.index', compact('tenant', 'palettes', 'layouts', 'themes'));
    }

    public function update(Request $request, UpdateTenantSettings $action): RedirectResponse
    {
        Gate::authorize('manage-tenant-settings');

        $tenant = app(TenantContext::class)->get();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'subdomain'     => ['required', new ValidSubdomain($tenant->id)],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'tax_percent'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'layout'        => ['required', 'in:' . implode(',', array_keys(config('branding.layouts')))],
            'theme'         => ['required', 'in:' . implode(',', array_keys(config('branding.themes')))],
            'color_palette' => ['required', 'in:' . implode(',', array_keys(config('branding.palettes')))],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        $subdomainChanged = $tenant->subdomain !== $data['subdomain'];

        $tenant = $action->handle($tenant, $data);

        // Redirect to new subdomain if it changed
        if ($subdomainChanged) {
            return redirect()
                ->away("http://{$tenant->subdomain}." . config('tenancy.central_domain') . '/settings')
                ->with('status', 'settings-updated');
        }

        return redirect()
            ->route('tenant.settings.edit', ['subdomain' => $tenant->subdomain])
            ->with('status', 'settings-updated');
    }
}
