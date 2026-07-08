<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class CreateInvitation
{
    public function handle(Tenant $tenant, string $email, string $role, User $inviter): string
    {
        // Reject if email already an active member
        $alreadyMember = $tenant->users()
            ->where('email', $email)
            ->wherePivot('status', 'active')
            ->exists();

        if ($alreadyMember) {
            throw new RuntimeException(__('Email ini sudah menjadi anggota aktif toko.'));
        }

        // Cancel any pending invitation for same tenant+email
        TenantInvitation::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $plain = Str::random(64);

        TenantInvitation::create([
            'tenant_id'  => $tenant->id,
            'email'      => strtolower(trim($email)),
            'role'       => $role,
            'token'      => hash('sha256', $plain),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        return $plain;
    }
}
