<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeArchivedTenantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_tenants_archived_beyond_the_retention_window(): void
    {
        config(['tenancy.archive_retention_days' => 30]);

        $old = Tenant::factory()->archived()->create(['archived_at' => now()->subDays(31)]);

        $this->artisan('tenants:purge-archived')->assertSuccessful();

        $this->assertDatabaseMissing('tenants', ['id' => $old->id]);
    }

    public function test_it_keeps_recently_archived_and_active_tenants(): void
    {
        config(['tenancy.archive_retention_days' => 30]);

        $recentlyArchived = Tenant::factory()->archived()->create(['archived_at' => now()->subDays(29)]);
        $active = Tenant::factory()->create();

        $this->artisan('tenants:purge-archived')->assertSuccessful();

        $this->assertDatabaseHas('tenants', ['id' => $recentlyArchived->id]);
        $this->assertDatabaseHas('tenants', ['id' => $active->id]);
    }

    public function test_dry_run_does_not_delete_anything(): void
    {
        $old = Tenant::factory()->archived()->create(['archived_at' => now()->subDays(60)]);

        $this->artisan('tenants:purge-archived', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('tenants', ['id' => $old->id]);
    }

    public function test_retention_window_is_configurable(): void
    {
        config(['tenancy.archive_retention_days' => 7]);

        $tenant = Tenant::factory()->archived()->create(['archived_at' => now()->subDays(8)]);

        $this->artisan('tenants:purge-archived')->assertSuccessful();

        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }
}
