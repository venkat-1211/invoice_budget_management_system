<?php

namespace App\Services\ExchangeRate;

use App\DTOs\ExchangeRateDTO;
use App\Repositories\Interfaces\ExchangeRateRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateApiService implements ExchangeRateServiceInterface
{
    private const API_URL = 'https://api.exchangerate-api.com/v4/latest/';
    private const CACHE_KEY = 'exchange_rates';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository
    ) {}

    public function fetchLatestRates(string $baseCurrency = 'USD'): array
    {
        $cacheKey = self::CACHE_KEY . "_{$baseCurrency}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseCurrency) {
            try {
                $response = Http::timeout(30)
                    ->retry(3, 100)
                    ->get(self::API_URL . $baseCurrency);

                if (!$response->successful()) {
                    Log::error('Exchange rate API failed', ['status' => $response->status()]);
                    return $this->getFallbackRates($baseCurrency);
                }

                $data = $response->json();
                $rates = [];
                $date = now()->toDateString();

                foreach ($data['rates'] as $currency => $rate) {
                    if ($currency === $baseCurrency) continue;

                    $dto = new ExchangeRateDTO(
                        base_currency: $baseCurrency,
                        target_currency: $currency,
                        rate: $rate,
                        rate_date: $date,
                        source: 'api'
                    );

                    $this->exchangeRateRepository->create($dto->toArray());
                    $rates[$currency] = $rate;
                }

                return $rates;
            } catch (\Exception $e) {
                Log::error('Exchange rate fetch error: ' . $e->getMessage());
                return $this->getFallbackRates($baseCurrency);
            }
        });
    }

    public function getRate(string $from, string $to): ?float
    {
        if ($from === $to) return 1.0;

        $cached = Cache::rememberForever("rate_{$from}_{$to}", function () use ($from, $to) {
            $rate = $this->exchangeRateRepository->getLatestRate($from, $to);
            return $rate ? (float) $rate->rate : null;
        });

        if ($cached) return $cached;

        // Try fetching fresh rates
        $rates = $this->fetchLatestRates($from);
        return $rates[$to] ?? null;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rate = $this->getRate($from, $to);
        return $rate ? round($amount * $rate, 2) : $amount;
    }

    private function getFallbackRates(string $baseCurrency): array
    {
        $latest = $this->exchangeRateRepository->getRatesByDate($baseCurrency, now()->toDateString());

        if ($latest->isEmpty()) {
            // Return yesterday's rates
            $latest = $this->exchangeRateRepository->getRatesByDate(
                $baseCurrency,
                now()->subDay()->toDateString()
            );
        }

        $rates = [];
        foreach ($latest as $rate) {
            $rates[$rate->target_currency] = (float) $rate->rate;
        }

        return $rates;
    }
}
