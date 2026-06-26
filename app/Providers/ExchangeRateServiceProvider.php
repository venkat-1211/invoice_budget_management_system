<?php

namespace App\Providers;

use App\Services\ExchangeRate\ExchangeRateApiService;
use App\Services\ExchangeRate\ExchangeRateServiceInterface;
use Illuminate\Support\ServiceProvider;

class ExchangeRateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExchangeRateServiceInterface::class, ExchangeRateApiService::class);
    }
}
