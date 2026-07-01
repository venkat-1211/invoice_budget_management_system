<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesInvoiceItemFactory extends Factory
{
    protected $model = \App\Models\SalesInvoiceItem::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 100);
        $price = fake()->randomFloat(2, 10, 1000);
        $taxRate = fake()->randomFloat(2, 0, 20);
        $discRate = fake()->randomFloat(2, 0, 15);
        $base = $qty * $price;
        $tax = round($base * ($taxRate / 100), 2);
        $disc = round($base * ($discRate / 100), 2);
        $total = $base + $tax - $disc;

        return [
            'sales_invoice_id' => \App\Models\SalesInvoice::factory(),
            'product_id' => \App\Models\Product::factory(),
            'description' => fake()->words(4, true),
            'quantity' => $qty,
            'unit' => fake()->randomElement(['pcs', 'kg', 'm', 'box']),
            'unit_price' => $price,
            'tax_rate' => $taxRate,
            'tax_amount' => $tax,
            'discount_percent' => $discRate,
            'discount_amount' => $disc,
            'total' => $total,
            'status' => 1,
        ];
    }
}
