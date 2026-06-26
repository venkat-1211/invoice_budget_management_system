<?php

namespace App\DTOs;

class SupplierDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $code = '',
        public readonly string $name = '',
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $tax_number = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $postal_code = null,
        public readonly string $payment_terms = 'Net 30',
        public readonly int $status = 1
    ) {}
}
