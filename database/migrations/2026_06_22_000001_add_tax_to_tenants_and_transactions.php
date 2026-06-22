<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('tax_percent', 5, 2)->default(0)->after('theme_config');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0)->after('cashier_id');
            $table->decimal('tax', 12, 2)->default(0)->after('subtotal');
        });

        // Backfill legacy rows: before tax existed, total == subtotal.
        DB::table('transactions')->update(['subtotal' => DB::raw('total')]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('tax_percent');
        });
    }
};
