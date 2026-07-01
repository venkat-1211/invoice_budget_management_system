<?php

use App\Services\DashboardService;
use App\Models\SalesInvoice;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Support\Facades\Cache;

describe('DashboardService', function () {
    beforeEach(function () {
        $this->service = app(DashboardService::class);
    });

    it('returns summary cards with correct data', function () {
        SalesInvoice::factory()->count(3)->create([
            'total_amount' => 1000,
            'paid_amount' => 500,
            'payment_status' => 'partial',
        ]);

        Expense::factory()->count(2)->create([
            'total_amount' => 300,
            'status' => 1,
        ]);

        $summary = $this->service->getSummaryCards();

        expect($summary)->toHaveKey('total_revenue');
        expect($summary)->toHaveKey('total_expenses');
        expect($summary)->toHaveKey('pending_invoices');
        expect($summary)->toHaveKey('total_customers');
    });

    it('caches summary data', function () {
        $summary1 = $this->service->getSummaryCards();
        $summary2 = $this->service->getSummaryCards();

        expect($summary1)->toBe($summary2);
    });

    it('returns revenue chart data for 6 months', function () {
        $chartData = $this->service->getRevenueChartData(6);

        expect($chartData)->toHaveKey('labels');
        expect($chartData)->toHaveKey('data');
        expect(count($chartData['labels']))->toBe(6);
        expect(count($chartData['data']))->toBe(6);
    });

    it('returns budget usage data', function () {
        Budget::factory()->count(3)->create([
            'allocated_amount' => 10000,
            'spent_amount' => 5000,
            'status' => 1,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $usage = $this->service->getBudgetUsageData();

        expect($usage)->toHaveKey('labels');
        expect($usage)->toHaveKey('allocated');
        expect($usage)->toHaveKey('spent');
    });

    it('returns recent activity', function () {
        $activity = $this->service->getRecentActivity();

        expect($activity)->toHaveKey('recent_invoices');
        expect($activity)->toHaveKey('recent_payments');
        expect($activity)->toHaveKey('recent_expenses');
    });
});
