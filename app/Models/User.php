<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\MembershipStatus;
use App\Enums\TenantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Whether the user has fully enabled (confirmed) two-factor authentication.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null
            && $this->two_factor_secret !== null;
    }

    /**
     * The user's recovery codes, decrypted into an array.
     *
     * @return list<string>
     */
    public function recoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode($this->two_factor_recovery_codes, true) ?: [];
    }

    /**
     * Consume a recovery code, removing it from the stored set.
     */
    public function replaceRecoveryCode(string $code): void
    {
        $remaining = array_values(array_filter(
            $this->recoveryCodes(),
            fn (string $stored) => ! hash_equals($stored, $code),
        ));

        $this->forceFill([
            'two_factor_recovery_codes' => json_encode($remaining),
        ])->save();
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->using(TenantUser::class)
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    /**
     * Return the user's active role in the given tenant, or null if not an active member.
     * Always queries fresh — callers rely on this for immediate revoke detection (E4).
     */
    public function roleFor(Tenant $tenant): ?TenantRole
    {
        $member = $this->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.id', $tenant->id)
            ->first();

        return $member?->pivot?->role;
    }

    public function isMemberOf(Tenant $tenant): bool
    {
        return $this->roleFor($tenant) !== null;
    }
}
