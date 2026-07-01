<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = \App\Models\Customer::class;

    public function definition(): array
    {
        return [
            'code' => 'CUST-' . fake()->unique()->randomNumber(5),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_number' => 'TAX-' . fake()->randomNumber(8),
            'billing_address' => fake()->address(),
            'shipping_address' => fake()->address(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'credit_limit' => fake()->randomFloat(2, 1000, 50000),
            'balance' => 0,
            'status' => 1,
        ];
    }

    public function highBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => fake()->randomFloat(2, 50000, 100000),
            'credit_limit' => 100000,
        ]);
    }

    public function noCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => 0,
            'balance' => 0,
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

    public function withUnicodeName(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '株式会社テスト ' . fake()->randomNumber(3),
        ]);
    }

    public function withEmojiName(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '🏢 Test Company ' . fake()->randomNumber(3),
        ]);
    }
}
