<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . fake()->unique()->randomNumber(6),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['product', 'service']),
            'category' => fake()->randomElement(['Electronics', 'Furniture', 'Office', 'Software', 'Hardware']),
            'unit' => fake()->randomElement(['pcs', 'kg', 'm', 'l', 'box', 'set']),
            'purchase_price' => fake()->randomFloat(2, 10, 1000),
            'sale_price' => fake()->randomFloat(2, 20, 2000),
            'tax_rate' => fake()->randomFloat(2, 0, 25),
            'stock_quantity' => fake()->numberBetween(0, 1000),
            'min_stock_level' => fake()->numberBetween(5, 50),
            'status' => 1,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 2,
            'min_stock_level' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'min_stock_level' => 10,
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'service',
            'stock_quantity' => 0,
            'min_stock_level' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_price' => 999999.99,
            'sale_price' => 1999999.99,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
