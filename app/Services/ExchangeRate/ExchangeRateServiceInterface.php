<?php

namespace App\Services\ExchangeRate;

interface ExchangeRateServiceInterface
{
    public function fetchLatestRates(string $baseCurrency = 'USD'): array;
    public function getRate(string $from, string $to): ?float;
    public function convert(float $amount, string $from, string $to): float;
}
