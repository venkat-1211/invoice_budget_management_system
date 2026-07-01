<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = \App\Models\Budget::class;

    public function definition(): array
    {
        $allocated = fake()->randomFloat(2, 1000, 100000);
        $spent = fake()->randomFloat(2, 0, $allocated * 0.8);

        return [
            'name' => fake()->words(3, true) . ' Budget',
            'type' => fake()->randomElement(['monthly', 'yearly', 'project']),
            'budget_year' => now()->year,
            'budget_month' => fake()->optional()->numberBetween(1, 12),
            'category' => fake()->randomElement(['Marketing', 'Operations', 'IT', 'HR', 'Sales']),
            'allocated_amount' => $allocated,
            'spent_amount' => $spent,
            'remaining_amount' => $allocated - $spent,
            'description' => fake()->optional()->paragraph(),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+2 months', '+1 year'),
            'created_by' => \App\Models\User::factory(),
            'status' => 1,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'monthly',
            'budget_month' => fake()->numberBetween(1, 12),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'yearly',
            'budget_month' => null,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);
    }

    public function overBudget(): static
    {
        return $this->state(function (array $attributes) {
            $spent = $attributes['allocated_amount'] * 1.5;
            return [
                'spent_amount' => $spent,
                'remaining_amount' => $attributes['allocated_amount'] - $spent,
            ];
        });
    }

    public function fullySpent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'spent_amount' => $attributes['allocated_amount'],
                'remaining_amount' => 0,
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => now()->subMonth(),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
