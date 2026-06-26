<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use App\Repositories\Interfaces\ExchangeRateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ExchangeRateRepository extends BaseRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    public function getLatestRate(string $base, string $target)
    {
        return DB::table('exchange_rates')
            ->where('base_currency', $base)
            ->where('target_currency', $target)
            ->where('rate_date', '<=', now())
            ->whereNull('deleted_at')
            ->orderByDesc('rate_date')
            ->first();
    }

    public function getRatesByDate(string $base, string $date)
    {
        return DB::table('exchange_rates')
            ->where('base_currency', $base)
            ->where('rate_date', $date)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getSupportedCurrencies()
    {
        return DB::table('exchange_rates')
            ->select('target_currency')
            ->where('base_currency', 'USD')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('target_currency');
    }
}
