<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule exchange rate updates
Schedule::call(function () {
    app(\App\Services\ExchangeRate\ExchangeRateServiceInterface::class)->fetchLatestRates('USD');
})->daily();

// Schedule cache warming
Schedule::command('cache:warm')->daily();
