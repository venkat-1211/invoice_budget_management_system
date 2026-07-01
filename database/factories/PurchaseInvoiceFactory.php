<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoiceFactory extends Factory
{
    protected $model = \App\Models\PurchaseInvoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $taxRate = fake()->randomFloat(2, 0, 20);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $discountAmount = fake()->randomFloat(2, 0, $subtotal * 0.2);
        $total = $subtotal + $taxAmount - $discountAmount;

        return [
            'invoice_number' => 'PI-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'supplier_id' => \App\Models\Supplier::factory(),
            'invoice_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+90 days'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'paid_amount' => 0,
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'exchange_rate' => 1.000000,
            'payment_status' => 'pending',
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => \App\Models\User::factory(),
            'status' => 1,
        ];
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'paid_amount' => $attributes['total_amount'],
                'payment_status' => 'paid',
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-90 days', '-1 day'),
            'payment_status' => 'pending',
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
