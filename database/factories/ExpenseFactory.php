<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = \App\Models\Expense::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 10, 5000);
        $tax = fake()->randomFloat(2, 0, $amount * 0.2);
        $total = $amount + $tax;

        return [
            'expense_number' => 'EXP-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'budget_id' => \App\Models\Budget::factory(),
            'supplier_id' => fake()->optional()->passthrough(\App\Models\Supplier::factory()),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'category' => fake()->randomElement(['Office', 'Travel', 'Utilities', 'Marketing', 'Salaries', 'Rent', 'Maintenance', 'Other']),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'credit_card', 'check']),
            'reference' => fake()->optional()->bothify('REF-####'),
            'amount' => $amount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'description' => fake()->optional()->paragraph(),
            'attachments' => null,
            'created_by' => \App\Models\User::factory(),
            'status' => 1,
        ];
    }

    public function withoutBudget(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget_id' => null,
        ]);
    }

    public function highAmount(): static
    {
        return $this->state(function (array $attributes) {
            $amount = 999999.99;
            return [
                'amount' => $amount,
                'tax_amount' => $amount * 0.1,
                'total_amount' => $amount * 1.1,
            ];
        });
    }

    public function futureDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'expense_date' => fake()->dateTimeBetween('+1 day', '+1 year'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
