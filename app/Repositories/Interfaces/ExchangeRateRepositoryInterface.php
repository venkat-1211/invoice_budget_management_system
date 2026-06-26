<?php

namespace App\Repositories\Interfaces;

interface ExchangeRateRepositoryInterface extends BaseRepositoryInterface
{
    public function getLatestRate(string $base, string $target);
    public function getRatesByDate(string $base, string $date);
    public function getSupportedCurrencies();
}
