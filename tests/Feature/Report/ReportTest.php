<?php

use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\User;

describe('Reports', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    describe('Revenue Report', function () {
        it('displays revenue report', function () {
            SalesInvoice::factory()->count(5)->create([
                'invoice_date' => now()->subDays(10),
                'total_amount' => 1000,
                'status' => 1,
            ]);

            $response = $this->get(route('reports.revenue', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $response->assertStatus(200);
            $response->assertViewIs('reports.revenue');
            $response->assertViewHas('report');
        });

        it('calculates total revenue correctly', function () {
            SalesInvoice::factory()->count(3)->create([
                'invoice_date' => now()->subDays(5),
                'total_amount' => 1000,
                'paid_amount' => 500,
                'status' => 1,
            ]);

            $response = $this->get(route('reports.revenue', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $response->assertStatus(200);
            $report = $response->viewData('report');
            expect($report['summary']['total_revenue'])->toBe(3000.0);
            expect($report['summary']['total_paid'])->toBe(1500.0);
        });

        it('filters by date range', function () {
            SalesInvoice::factory()->create([
                'invoice_date' => now()->subMonths(2),
                'total_amount' => 1000,
                'status' => 1,
            ]);
            SalesInvoice::factory()->create([
                'invoice_date' => now()->subDays(5),
                'total_amount' => 2000,
                'status' => 1,
            ]);

            $response = $this->get(route('reports.revenue', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $report = $response->viewData('report');
            expect($report['summary']['total_revenue'])->toBe(2000.0);
        });

        it('handles empty date range', function () {
            $response = $this->get(route('reports.revenue', [
                'start_date' => now()->addMonth()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
            ]));

            $response->assertStatus(200);
            $report = $response->viewData('report');
            expect($report['summary']['total_revenue'])->toBe(0.0);
        });

        it('caches report data', function () {
            SalesInvoice::factory()->create([
                'invoice_date' => now()->subDays(5),
                'total_amount' => 1000,
                'status' => 1,
            ]);

            $response1 = $this->get(route('reports.revenue', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $response2 = $this->get(route('reports.revenue', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $report1 = $response1->viewData('report');
            $report2 = $response2->viewData('report');
            expect($report1['summary']['total_revenue'])->toBe($report2['summary']['total_revenue']);
        });
    });

    describe('Expense Report', function () {
        it('displays expense report', function () {
            Expense::factory()->count(5)->create([
                'expense_date' => now()->subDays(5),
                'total_amount' => 500,
                'status' => 1,
            ]);

            $response = $this->get(route('reports.expenses', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $response->assertStatus(200);
            $report = $response->viewData('report');
            expect($report['summary']['total_expenses'])->toBe(2500.0);
        });

        it('groups by category', function () {
            Expense::factory()->count(3)->create([
                'category' => 'Office',
                'expense_date' => now()->subDays(5),
                'total_amount' => 100,
            ]);
            Expense::factory()->count(2)->create([
                'category' => 'Travel',
                'expense_date' => now()->subDays(5),
                'total_amount' => 200,
            ]);

            $response = $this->get(route('reports.expenses', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $report = $response->viewData('report');
            expect($report['by_category'])->toHaveCount(2);
        });
    });

    describe('Budget Report', function () {
        it('displays budget report for year', function () {
            Budget::factory()->count(5)->create([
                'budget_year' => 2024,
                'allocated_amount' => 10000,
                'spent_amount' => 5000,
            ]);

            $response = $this->get(route('reports.budgets', [
                'year' => 2024,
            ]));

            $response->assertStatus(200);
            $report = $response->viewData('report');
            expect($report['summary']['total_allocated'])->toBe(50000.0);
            expect($report['summary']['total_spent'])->toBe(25000.0);
        });

        it('shows monthly breakdown', function () {
            Budget::factory()->monthly()->create([
                'budget_year' => 2024,
                'budget_month' => 1,
                'allocated_amount' => 10000,
            ]);

            $response = $this->get(route('reports.budgets', [
                'year' => 2024,
            ]));

            $report = $response->viewData('report');
            expect($report['monthly_breakdown'])->toHaveCount(12);
            expect($report['monthly_breakdown'][1]['allocated'])->toBe(10000.0);
        });

        it('detects over budget budgets', function () {
            Budget::factory()->overBudget()->create([
                'budget_year' => 2024,
            ]);

            $response = $this->get(route('reports.budgets', [
                'year' => 2024,
            ]));

            $report = $response->viewData('report');
            expect($report['summary']['over_budget_count'])->toBe(1);
        });
    });

    describe('Profit Report', function () {
        it('calculates profit correctly', function () {
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

            $response = $this->get(route('reports.profit', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $response->assertStatus(200);
            $report = $response->viewData('report');
            expect($report['revenue'])->toBe(20000.0);
            expect($report['cost_of_goods_sold'])->toBe(10000.0);
            expect($report['gross_profit'])->toBe(10000.0);
            expect($report['operating_expenses'])->toBe(2000.0);
            expect($report['net_profit'])->toBe(8000.0);
            expect($report['gross_margin'])->toBe(50.0);
            expect($report['net_margin'])->toBe(40.0);
        });

        it('handles zero revenue', function () {
            $response = $this->get(route('reports.profit', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $report = $response->viewData('report');
            expect($report['gross_margin'])->toBe(0.0);
            expect($report['net_margin'])->toBe(0.0);
        });

        it('handles negative profit', function () {
            SalesInvoice::factory()->create([
                'invoice_date' => now()->subDays(5),
                'total_amount' => 1000,
                'status' => 1,
            ]);

            PurchaseInvoice::factory()->create([
                'invoice_date' => now()->subDays(5),
                'total_amount' => 2000,
                'status' => 1,
            ]);

            Expense::factory()->create([
                'expense_date' => now()->subDays(5),
                'total_amount' => 1000,
                'status' => 1,
            ]);

            $response = $this->get(route('reports.profit', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

            $report = $response->viewData('report');
            expect($report['net_profit'])->toBe(-2000.0);
            expect($report['net_margin'])->toBe(-200.0);
        });
    });
});
