<?php

namespace App\DTOs;

class ProductDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $sku = '',
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly string $type = 'product',
        public readonly ?string $category = null,
        public readonly string $unit = 'pcs',
        public readonly float $purchase_price = 0,
        public readonly float $sale_price = 0,
        public readonly float $tax_rate = 0,
        public readonly int $stock_quantity = 0,
        public readonly int $min_stock_level = 0,
        public readonly int $status = 1
    ) {}
}
