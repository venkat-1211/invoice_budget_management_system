<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExchangeRateFactory extends Factory
{
    protected $model = \App\Models\ExchangeRate::class;

    public function definition(): array
    {
        return [
            'base_currency' => 'USD',
            'target_currency' => fake()->randomElement(['EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF']),
            'rate' => fake()->randomFloat(6, 0.5, 2),
            'rate_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'source' => fake()->randomElement(['api', 'manual']),
            'status' => 1,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'manual',
        ]);
    }

    public function api(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'api',
        ]);
    }

    public function outdated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rate_date' => fake()->dateTimeBetween('-90 days', '-31 days'),
        ]);
    }
}
