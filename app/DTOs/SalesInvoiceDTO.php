<?php

namespace App\DTOs;

class SalesInvoiceDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $invoice_number = '',
        public readonly int $customer_id = 0,
        public readonly string $invoice_date = '',
        public readonly string $due_date = '',
        public readonly float $subtotal = 0,
        public readonly float $tax_amount = 0,
        public readonly float $discount_amount = 0,
        public readonly float $total_amount = 0,
        public readonly float $paid_amount = 0,
        public readonly string $currency = 'USD',
        public readonly float $exchange_rate = 1,
        public readonly string $payment_status = 'pending',
        public readonly ?string $reference = null,
        public readonly ?string $notes = null,
        public readonly int $created_by = 0,
        public readonly int $status = 1,
        public readonly array $items = []
    ) {}
}
