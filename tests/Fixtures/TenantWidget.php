<?php

namespace Tests\Fixtures;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Test-only tenant-scoped model used to verify the BelongsToTenant isolation
 * mechanism without depending on domain tables introduced in later milestones.
 * Its table is created on the fly in TenantIsolationTest::setUp().
 */
class TenantWidget extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_widgets';

    protected $fillable = ['name', 'tenant_id'];
}
