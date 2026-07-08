<?php

namespace App\Actions;

use App\Enums\MembershipStatus;
use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateTenant
{
    /**
     * Create a new tenant, store its logo, and attach the owner membership —
     * all inside a single DB transaction. Orphaned logo files are cleaned up
     * on failure.
     */
    public function handle(
        User $owner,
        array $data,
        array $themeConfig,
        ?int $templateId = null,
    ): Tenant {
        $logoPath = null;

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $logoPath = $data['logo']->store('logos', 'public');
        }

        try {
            return DB::transaction(function () use ($owner, $data, $themeConfig, $templateId, $logoPath): Tenant {
                $tenant = Tenant::create([
                    'owner_id'    => $owner->id,
                    'name'        => $data['name'],
                    'locale'      => $data['locale'] ?? null,
                    'subdomain'   => $data['subdomain'],
                    'logo_path'   => $logoPath,
                    'status'      => Tenant::STATUS_ACTIVE,
                    'template_id' => $templateId,
                    'theme_config' => $themeConfig,
                ]);

                $tenant->users()->attach($owner->id, [
                    'role'   => TenantRole::Owner->value,
                    'status' => MembershipStatus::Active->value,
                ]);

                return $tenant;
            });
        } catch (\Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            throw $e;
        }
    }
}
