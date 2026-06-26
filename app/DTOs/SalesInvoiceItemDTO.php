<?php

namespace App\DTOs;

class SalesInvoiceItemDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly int $sales_invoice_id = 0,
        public readonly ?int $product_id = null,
        public readonly string $description = '',
        public readonly float $quantity = 0,
        public readonly string $unit = 'pcs',
        public readonly float $unit_price = 0,
        public readonly float $tax_rate = 0,
        public readonly float $discount_percent = 0,
        public readonly int $status = 1
    ) {}
}
