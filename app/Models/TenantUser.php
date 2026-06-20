<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\TenantRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantUser extends Pivot
{
    /** @use HasFactory<\Database\Factories\TenantUserFactory> */
    use HasFactory;

    protected $table = 'tenant_user';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role' => TenantRole::class,
            'status' => MembershipStatus::class,
        ];
    }
}
