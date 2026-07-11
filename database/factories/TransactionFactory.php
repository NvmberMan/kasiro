<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 500000);
        $paid  = $total + fake()->numberBetween(0, 50000);

        return [
            'tenant_id'      => null,
            'cashier_id'     => null,
            'total'          => $total,
            'paid'           => $paid,
            'change'         => $paid - $total,
            'transacted_at'  => now(),
        ];
    }
}
