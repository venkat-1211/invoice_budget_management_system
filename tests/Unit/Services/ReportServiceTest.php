<?php

use App\Services\ReportService;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use App\Models\Budget;

describe('ReportService', function () {
    beforeEach(function () {
        $this->service = app(ReportService::class);
    });

    it('generates revenue report for period', function () {
        SalesInvoice::factory()->count(5)->create([
            'invoice_date' => now()->subDays(10),
            'total_amount' => 1000,
            'paid_amount' => 500,
            'status' => 1,
        ]);

        $report = $this->service->getRevenueReport(
            now()->subMonth()->toDateString(),
            now()->toDateString()
        );

        expect($report)->toHaveKey('summary');
        expect($report)->toHaveKey('daily_breakdown');
        expect($report)->toHaveKey('by_customer');
        expect($report['summary']['total_revenue'])->toBe(5000.0);
    });

    it('generates expense report for period', function () {
        Expense::factory()->count(3)->create([
            'expense_date' => now()->subDays(5),
            'total_amount' => 200,
            'status' => 1,
        ]);

        $report = $this->service->getExpenseReport(
            now()->subMonth()->toDateString(),
            now()->toDateString()
        );

        expect($report['summary']['total_expenses'])->toBe(600.0);
    });

    it('generates budget report for year', function () {
        Budget::factory()->count(4)->create([
            'budget_year' => now()->year,
            'allocated_amount' => 10000,
            'spent_amount' => 6000,
        ]);

        $report = $this->service->getBudgetReport(now()->year);

        expect($report)->toHaveKey('summary');
        expect($report)->toHaveKey('budgets');
        expect($report)->toHaveKey('monthly_breakdown');
        expect($report['summary']['total_allocated'])->toBe(40000.0);
    });

    it('generates profit report correctly', function () {
        SalesInvoice::factory()->count(2)->create([
            'invoice_date' => now()->subDays(5),
            'total_amount' => 10000,
            'status' => 1,
        ]);

        PurchaseInvoice::factory()->count(2)->create([
            'invoice_date' => now()->subDays(5),
            'total_amount' => 5000,
            'status' => 1,
        ]);

        Expense::factory()->count(2)->create([
            'expense_date' => now()->subDays(5),
            'total_amount' => 1000,
            'status' => 1,
        ]);

        $report = $this->service->getProfitReport(
            now()->subMonth()->toDateString(),
            now()->toDateString()
        );

        expect($report['revenue'])->toBe(20000.0);
        expect($report['cost_of_goods_sold'])->toBe(10000.0);
        expect($report['gross_profit'])->toBe(10000.0);
        expect($report['operating_expenses'])->toBe(2000.0);
        expect($report['net_profit'])->toBe(8000.0);
    });

    it('handles zero revenue gracefully', function () {
        $report = $this->service->getProfitReport(
            now()->subMonth()->toDateString(),
            now()->toDateString()
        );

        expect($report['gross_margin'])->toBe(0.0);
        expect($report['net_margin'])->toBe(0.0);
    });
});
