<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}



uses()->group('unit')->in('Unit');
uses()->group('feature')->in('Feature');
uses()->group('integration')->in('Integration');
uses()->group('api')->in('Api');
uses()->group('security')->in('Security');
uses()->group('performance')->in('Performance');

uses(RefreshDatabase::class)->in('Feature', 'Integration', 'Api', 'Security', 'Performance');

beforeEach(function () {
    // Clear caches before each test
    \Illuminate\Support\Facades\Cache::flush();
    \Illuminate\Support\Facades\RateLimiter::clear('login|' . request()->ip());
    \Illuminate\Support\Facades\RateLimiter::clear('purchase-invoice-create|' . request()->ip());
    \Illuminate\Support\Facades\RateLimiter::clear('sales-invoice-create|' . request()->ip());
    \Illuminate\Support\Facades\RateLimiter::clear('payment-create|' . request()->ip());
});

// Custom expectations
expect()->extend('toBeValidUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

expect()->extend('toBeDecimal', function (int $precision = 2) {
    return $this->toMatch('/^\d+\.\d{' . $precision . '}$/');
});
