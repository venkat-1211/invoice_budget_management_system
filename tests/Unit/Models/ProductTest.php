<?php

use App\Models\Product;

describe('Product Model', function () {
    it('detects low stock correctly', function () {
        $product = Product::factory()->lowStock()->create();

        expect($product->isLowStock())->toBeTrue();
    });

    it('does not flag normal stock as low', function () {
        $product = Product::factory()->create([
            'stock_quantity' => 100,
            'min_stock_level' => 10,
        ]);

        expect($product->isLowStock())->toBeFalse();
    });

    it('detects out of stock', function () {
        $product = Product::factory()->outOfStock()->create();

        expect($product->stock_quantity)->toBe(0);
        expect($product->isLowStock())->toBeTrue();
    });

    it('handles high value products', function () {
        $product = Product::factory()->highValue()->create();

        expect($product->sale_price)->toBeGreaterThan(1000000);
    });

    it('handles services with zero stock', function () {
        $product = Product::factory()->service()->create();

        expect($product->type)->toBe('service');
        expect($product->stock_quantity)->toBe(0);
    });
});
