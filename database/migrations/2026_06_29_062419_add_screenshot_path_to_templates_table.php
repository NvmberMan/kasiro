<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // Relative path to the generated POS preview screenshot on the public
            // disk (e.g. screenshots/template-kedai-kopi.jpg). Mirrors the
            // tenants.screenshot_path convention.
            $table->string('screenshot_path')->nullable()->after('preview_image');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('screenshot_path');
        });
    }
};
