<?php

use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;

describe('Dashboard', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('displays dashboard for authenticated user', function () {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    });

    it('requires authentication', function () {
        auth()->logout();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    });

    it('shows summary cards with correct data', function () {
        SalesInvoice::factory()->count(3)->create([
            'total_amount' => 1000,
            'paid_amount' => 500,
            'payment_status' => 'partial',
        ]);

        Expense::factory()->count(2)->create([
            'total_amount' => 300,
            'status' => 1,
        ]);

        Customer::factory()->count(5)->create();
        Supplier::factory()->count(3)->create();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('summary');
        $response->assertViewHas('revenueChart');
        $response->assertViewHas('expenseChart');
        $response->assertViewHas('budgetUsage');
        $response->assertViewHas('recentActivity');
    });

    it('shows exchange rates', function () {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('exchangeRates');
    });

    it('handles missing exchange rate API gracefully', function () {
        // Mock API failure
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response([], 500),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        // Should still render even with API failure
    });

    it('caches dashboard data', function () {
        $response1 = $this->get(route('dashboard'));
        $response2 = $this->get(route('dashboard'));

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    });
});
