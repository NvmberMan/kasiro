<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner    = User::factory()->create();
        $this->tenant   = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->employee = User::factory()->create();
        $this->tenant->users()->attach($this->employee, ['role' => 'cashier', 'status' => 'active']);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    public function test_owner_can_view_employees_page(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/employees'))
            ->assertOk()
            ->assertSee($this->employee->name);
    }

    public function test_cashier_cannot_view_employees_page(): void
    {
        $this->actingAs($this->employee)
            ->get($this->url('/employees'))
            ->assertForbidden();
    }

    public function test_owner_can_change_employee_role(): void
    {
        $this->actingAs($this->owner)
            ->patch($this->url("/employees/{$this->employee->id}"), ['role' => 'manager'])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->employee->id,
            'role'      => 'manager',
        ]);
    }

    public function test_owner_cannot_change_their_own_role(): void
    {
        $this->actingAs($this->owner)
            ->patch($this->url("/employees/{$this->owner->id}"), ['role' => 'cashier'])
            ->assertSessionHasErrors();
    }

    public function test_owner_can_revoke_employee(): void
    {
        $this->actingAs($this->owner)
            ->delete($this->url("/employees/{$this->employee->id}"))
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->employee->id,
            'status'    => 'revoked',
        ]);
    }

    public function test_revoked_employee_loses_access_immediately(): void
    {
        $this->actingAs($this->owner)
            ->delete($this->url("/employees/{$this->employee->id}"));

        $this->actingAs($this->employee)
            ->get($this->url('/pos'))
            ->assertForbidden();
    }

    public function test_owner_cannot_revoke_themselves(): void
    {
        $this->actingAs($this->owner)
            ->delete($this->url("/employees/{$this->owner->id}"))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->owner->id,
            'status'    => 'active',
        ]);
    }
}
