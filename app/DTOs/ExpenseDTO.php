<?php

namespace App\DTOs;

class ExpenseDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $expense_number = '',
        public readonly ?int $budget_id = null,
        public readonly ?int $supplier_id = null,
        public readonly string $expense_date = '',
        public readonly string $category = '',
        public readonly string $payment_method = 'cash',
        public readonly ?string $reference = null,
        public readonly float $amount = 0,
        public readonly float $tax_amount = 0,
        public readonly float $total_amount = 0,
        public readonly string $currency = 'USD',
        public readonly ?string $description = null,
        public readonly ?array $attachments = null,
        public readonly int $created_by = 0,
        public readonly int $status = 1
    ) {}
}
