<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantUser>
 */
class TenantUserFactory extends Factory
{
    protected $model = TenantUser::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'role' => TenantRole::Cashier,
            'status' => MembershipStatus::Active,
        ];
    }

    public function owner(): static
    {
        return $this->state(['role' => TenantRole::Owner]);
    }

    public function manager(): static
    {
        return $this->state(['role' => TenantRole::Manager]);
    }

    public function revoked(): static
    {
        return $this->state(['status' => MembershipStatus::Revoked]);
    }
}
