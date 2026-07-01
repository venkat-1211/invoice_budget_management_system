<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = \App\Models\Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'payable_type' => \App\Models\SalesInvoice::class,
            'payable_id' => \App\Models\SalesInvoice::factory(),
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'credit_card', 'check', 'online']),
            'reference' => fake()->optional()->bothify('REF-####'),
            'transaction_id' => fake()->optional()->uuid(),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => \App\Models\User::factory(),
            'status' => 1,
        ];
    }

    public function forPurchaseInvoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'payable_type' => \App\Models\PurchaseInvoice::class,
            'payable_id' => \App\Models\PurchaseInvoice::factory(),
        ]);
    }

    public function forSalesInvoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'payable_type' => \App\Models\SalesInvoice::class,
            'payable_id' => \App\Models\SalesInvoice::factory(),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
