<?php

use App\Services\ExchangeRate\ExchangeRateApiService;
use App\Repositories\Interfaces\ExchangeRateRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

describe('ExchangeRateApiService', function () {
    beforeEach(function () {
        $this->mockRepo = Mockery::mock(ExchangeRateRepositoryInterface::class);
        $this->service = new ExchangeRateApiService($this->mockRepo);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('fetches rates from API successfully', function () {
        Http::fake([
            'https://api.exchangerate-api.com/v4/latest/USD' => Http::response([
                'rates' => [
                    'EUR' => 0.92,
                    'GBP' => 0.79,
                    'JPY' => 150.25,
                ],
            ], 200),
        ]);

        $this->mockRepo->shouldReceive('create')->times(3);

        $rates = $this->service->fetchLatestRates('USD');

        expect($rates)->toHaveKey('EUR');
        expect($rates['EUR'])->toBe(0.92);
        expect($rates)->toHaveKey('GBP');
    });

    it('returns cached rates when available', function () {
        Cache::put('exchange_rates_USD', ['EUR' => 0.95], 3600);

        $rates = $this->service->fetchLatestRates('USD');

        expect($rates['EUR'])->toBe(0.95);
    });

    it('handles API failure gracefully', function () {
        Http::fake([
            'https://api.exchangerate-api.com/v4/latest/USD' => Http::response([], 500),
        ]);

        $this->mockRepo->shouldReceive('getRatesByDate')->andReturn(collect([
            (object)['target_currency' => 'EUR', 'rate' => 0.90],
        ]));

        $rates = $this->service->fetchLatestRates('USD');

        expect($rates)->toHaveKey('EUR');
    });

    it('converts currency correctly', function () {
        $this->mockRepo->shouldReceive('getLatestRate')
            ->with('USD', 'EUR')
            ->andReturn((object)['rate' => 0.92]);

        $result = $this->service->convert(100, 'USD', 'EUR');

        expect($result)->toBe(92.0);
    });

    it('returns same amount when converting same currency', function () {
        $result = $this->service->convert(100, 'USD', 'USD');

        expect($result)->toBe(100.0);
    });

    it('returns null rate when not found', function () {
        $this->mockRepo->shouldReceive('getLatestRate')
            ->andReturn(null);

        $this->mockRepo->shouldReceive('getRatesByDate')->andReturn(collect([]));

        $rate = $this->service->getRate('USD', 'XYZ');

        expect($rate)->toBeNull();
    });
});
