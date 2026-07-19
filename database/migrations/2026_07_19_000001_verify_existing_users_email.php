<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Email verification is being turned on (User now implements MustVerifyEmail).
     * Grandfather every account that already exists so current users — including
     * the seeded demo account — are treated as verified and are never locked out
     * behind the verification wall. Only registrations created after this point
     * start life unverified and must click the verification link.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Intentionally irreversible: we cannot know which accounts were
        // unverified before this ran, and re-nulling them would lock users out.
    }
};
