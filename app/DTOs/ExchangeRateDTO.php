<?php

namespace App\DTOs;

class ExchangeRateDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly string $base_currency = 'USD',
        public readonly string $target_currency = '',
        public readonly float $rate = 0,
        public readonly string $rate_date = '',
        public readonly string $source = 'api',
        public readonly int $status = 1
    ) {}
}
