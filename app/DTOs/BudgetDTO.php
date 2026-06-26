<?php

namespace App\DTOs;

class BudgetDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $name = '',
        public readonly string $type = 'monthly',
        public readonly int $budget_year = 0,
        public readonly ?int $budget_month = null,
        public readonly ?string $category = null,
        public readonly float $allocated_amount = 0,
        public readonly float $spent_amount = 0,
        public readonly float $remaining_amount = 0,
        public readonly ?string $description = null,
        public readonly string $start_date = '',
        public readonly string $end_date = '',
        public readonly int $created_by = 0,
        public readonly int $status = 1
    ) {}
}
