<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ArchiveTenant;
use App\Actions\RestoreTenant;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TenantArchiveController extends Controller
{
    public function store(Request $request, Tenant $tenant, ArchiveTenant $action): RedirectResponse
    {
        Gate::authorize('archive', $tenant);

        $action->handle($tenant);

        return redirect()->route('my-stores')->with('status', 'tenant-archived');
    }

    public function destroy(Request $request, Tenant $tenant, RestoreTenant $action): RedirectResponse
    {
        Gate::authorize('restore', $tenant);

        $action->handle($tenant);

        return redirect()->route('archive')->with('status', 'tenant-restored');
    }
}
