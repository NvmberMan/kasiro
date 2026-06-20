<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Milestone 1 subset of the tenants table (PRD §10). Columns tied to
     * later milestones (owner_id FK, template_id FK, logo_path) are kept
     * nullable / deferred so this foundation can land independently.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            // Owner FK is wired in Milestone 2 (auth). Nullable for now.
            $table->foreignId('owner_id')->nullable()->index();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active')->index();
            // template_id FK added with the templates table in Milestone 3.
            $table->unsignedBigInteger('template_id')->nullable()->index();
            $table->json('theme_config')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
