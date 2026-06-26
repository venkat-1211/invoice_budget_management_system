<?php

namespace App\DTOs;

class PaymentDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $payment_number = '',
        public readonly string $payable_type = '',
        public readonly int $payable_id = 0,
        public readonly string $payment_date = '',
        public readonly float $amount = 0,
        public readonly string $payment_method = 'cash',
        public readonly ?string $reference = null,
        public readonly ?string $transaction_id = null,
        public readonly ?string $notes = null,
        public readonly int $created_by = 0,
        public readonly int $status = 1
    ) {}
}
