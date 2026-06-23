<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTenantCustomRequest;
use App\Http\Requests\CreateTenantFromTemplateRequest;
use App\Models\Template;
use App\Models\Tenant;
use App\Support\ThemeConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreateTenantController extends Controller
{
    public function chooseFlow(): View
    {
        return view('tenants.choose');
    }

    public function createCustom(): View
    {
        return view('tenants.create-custom', [
            'layouts'  => config('branding.layouts'),
            'themes'   => config('branding.themes'),
            'palettes' => config('branding.palettes'),
            'defaults' => config('branding.defaults'),
        ]);
    }

    public function storeCustom(CreateTenantCustomRequest $request, CreateTenant $action): RedirectResponse
    {
        $themeConfig = ThemeConfig::fromCustomInput($request->validated());
        $tenant = $action->handle(
            owner: $request->user(),
            data: $request->validated(),
            themeConfig: $themeConfig,
            templateId: null,
        );

        return redirect()->route('tenants.created', $tenant);
    }

    public function createFromTemplate(): View
    {
        return view('tenants.create-template', [
            'templates' => Template::published()->get(),
        ]);
    }

    public function storeFromTemplate(CreateTenantFromTemplateRequest $request, CreateTenant $action): RedirectResponse
    {
        $template = Template::published()->findOrFail($request->validated('template_id'));
        $themeConfig = ThemeConfig::fromTemplate($template);

        $tenant = $action->handle(
            owner: $request->user(),
            data: $request->validated(),
            themeConfig: $themeConfig,
            templateId: $template->id,
        );

        return redirect()->route('tenants.created', $tenant);
    }

    public function created(Tenant $tenant): View
    {
        abort_unless(
            $tenant->owner_id === auth()->id(),
            403,
        );

        return view('tenants.created', compact('tenant'));
    }

    public function showcase(): View
    {
        return view('tenants.showcase', [
            'templates' => Template::published()->get(),
        ]);
    }

    public function storeFromShowcase(CreateTenantFromTemplateRequest $request, CreateTenant $action): RedirectResponse
    {
        $template = Template::published()->findOrFail($request->validated('template_id'));
        $themeConfig = ThemeConfig::fromTemplate($template);

        $tenant = $action->handle(
            owner: $request->user(),
            data: $request->validated(),
            themeConfig: $themeConfig,
            templateId: $template->id,
        );

        return redirect()->route('tenants.created', $tenant);
    }
}
