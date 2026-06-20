<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total', 12, 2);
            $table->decimal('paid', 12, 2);
            $table->decimal('change', 12, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->timestamp('transacted_at');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'transacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
