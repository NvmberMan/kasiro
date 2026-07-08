<?php

namespace App\Actions;

use App\Models\TenantInvitation;
use App\Models\User;
use RuntimeException;

class AcceptInvitation
{
    public function handle(string $plainToken, User $user): TenantInvitation
    {
        $invitation = TenantInvitation::where('token', hash('sha256', $plainToken))->first();

        if (! $invitation) {
            throw new RuntimeException(__('Undangan tidak ditemukan atau sudah tidak valid.'));
        }

        if ($invitation->isAccepted()) {
            throw new RuntimeException(__('Undangan ini sudah pernah digunakan.'));
        }

        if ($invitation->isExpired()) {
            throw new RuntimeException(__('Undangan sudah kedaluwarsa.'));
        }

        $tenant = $invitation->tenant;

        $alreadyMember = $tenant->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();

        if ($alreadyMember) {
            throw new RuntimeException(__('Anda sudah menjadi anggota toko ini.'));
        }

        // Attach or update pivot
        $existing = $tenant->users()->where('users.id', $user->id)->first();

        if ($existing) {
            $tenant->users()->updateExistingPivot($user->id, [
                'role'   => $invitation->role,
                'status' => 'active',
            ]);
        } else {
            $tenant->users()->attach($user->id, [
                'role'   => $invitation->role,
                'status' => 'active',
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        return $invitation;
    }
}
