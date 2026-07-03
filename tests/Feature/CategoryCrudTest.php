<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    public function test_owner_can_view_category_list(): void
    {
        Category::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Makanan']);

        $this->actingAs($this->owner)
            ->get($this->url('/categories'))
            ->assertOk()
            ->assertSee('Makanan');
    }

    public function test_cashier_can_view_category_list(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/categories'))
            ->assertOk();
    }

    public function test_owner_can_create_category(): void
    {
        $this->actingAs($this->owner)
            ->post($this->url('/categories'), ['name' => 'Minuman'])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Minuman',
        ]);
    }

    public function test_cashier_cannot_create_category(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/categories'), ['name' => 'Minuman'])
            ->assertForbidden();
    }

    public function test_owner_can_update_category(): void
    {
        $cat = Category::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Lama']);

        $this->actingAs($this->owner)
            ->put($this->url("/categories/{$cat->id}"), ['name' => 'Baru'])
            ->assertRedirect();

        $this->assertEquals('Baru', $cat->fresh()->name);
    }

    public function test_cashier_cannot_update_category(): void
    {
        $cat = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->cashier)
            ->put($this->url("/categories/{$cat->id}"), ['name' => 'Baru'])
            ->assertForbidden();
    }

    public function test_owner_can_delete_category(): void
    {
        $cat = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->owner)
            ->delete($this->url("/categories/{$cat->id}"))
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_category_is_isolated_per_tenant(): void
    {
        $other  = Tenant::factory()->create();
        $catOther = Category::factory()->create(['tenant_id' => $other->id, 'name' => 'Milik Lain']);

        // owner of $this->tenant should get 404 when accessing other tenant's category
        $this->actingAs($this->owner)
            ->put($this->url("/categories/{$catOther->id}"), ['name' => 'Hack'])
            ->assertStatus(404);
    }
}
