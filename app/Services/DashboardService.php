<?php

namespace App\Services;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Interfaces\SalesInvoiceRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private SalesInvoiceRepositoryInterface $salesInvoiceRepo,
        private PurchaseInvoiceRepositoryInterface $purchaseInvoiceRepo,
        private ExpenseRepositoryInterface $expenseRepo,
        private BudgetRepositoryInterface $budgetRepo,
        private CustomerRepositoryInterface $customerRepo
    ) {}

    public function getSummaryCards(): array
    {
        return Cache::remember('dashboard_summary', 300, function () {
            $currentMonth = now()->format('Y-m');
            $lastMonth = now()->subMonth()->format('Y-m');

            return [
                'total_revenue' => $this->getMonthlyRevenue($currentMonth),
                'total_expenses' => $this->getMonthlyExpenses($currentMonth),
                'pending_invoices' => DB::table('sales_invoices')
                    ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                    ->whereNull('deleted_at')
                    ->count(),
                'overdue_invoices' => DB::table('sales_invoices')
                    ->where('due_date', '<', now())
                    ->whereIn('payment_status', ['pending', 'partial'])
                    ->whereNull('deleted_at')
                    ->count(),
                'total_customers' => DB::table('customers')->whereNull('deleted_at')->count(),
                'total_suppliers' => DB::table('suppliers')->whereNull('deleted_at')->count(),
                'revenue_change' => $this->calculateChange(
                    $this->getMonthlyRevenue($currentMonth),
                    $this->getMonthlyRevenue($lastMonth)
                ),
                'expense_change' => $this->calculateChange(
                    $this->getMonthlyExpenses($currentMonth),
                    $this->getMonthlyExpenses($lastMonth)
                ),
            ];
        });
    }

    public function getRevenueChartData(int $months = 6): array
    {
        return Cache::remember("revenue_chart_{$months}", 600, function () use ($months) {
            $data = [];
            $labels = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M Y');

                $revenue = DB::table('sales_invoices')
                    ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$monthKey])
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->sum('total_amount');

                $data[] = (float) $revenue;
            }

            return ['labels' => $labels, 'data' => $data];
        });
    }

    public function getExpenseChartData(int $months = 6): array
    {
        return Cache::remember("expense_chart_{$months}", 600, function () use ($months) {
            $data = [];
            $labels = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M Y');

                $expenses = DB::table('expenses')
                    ->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$monthKey])
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->sum('total_amount');

                $data[] = (float) $expenses;
            }

            return ['labels' => $labels, 'data' => $data];
        });
    }

    public function getBudgetUsageData(): array
    {
        return Cache::remember('budget_usage', 300, function () {
            $budgets = DB::table('budgets')
                ->where('status', 1)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereNull('deleted_at')
                ->get();

            return [
                'labels' => $budgets->pluck('name')->toArray(),
                'allocated' => $budgets->pluck('allocated_amount')->map(fn($v) => (float)$v)->toArray(),
                'spent' => $budgets->pluck('spent_amount')->map(fn($v) => (float)$v)->toArray(),
            ];
        });
    }

    public function getRecentActivity(): array
    {
        return [
            'recent_invoices' => $this->salesInvoiceRepo->getRecentInvoices(5),
            'recent_payments' => DB::table('payments')
                ->select('payments.*', 'sales_invoices.invoice_number as invoice_ref')
                ->leftJoin('sales_invoices', function ($join) {
                    $join->on('payments.payable_id', '=', 'sales_invoices.id')
                         ->where('payments.payable_type', '=', 'App\\Models\\SalesInvoice');
                })
                ->whereNull('payments.deleted_at')
                ->orderByDesc('payments.created_at')
                ->limit(5)
                ->get(),
            'recent_expenses' => $this->expenseRepo->getRecentExpenses(5),
        ];
    }

    private function getMonthlyRevenue(string $month): float
    {
        return (float) DB::table('sales_invoices')
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->sum('total_amount');
    }

    private function getMonthlyExpenses(string $month): float
    {
        return (float) DB::table('expenses')
            ->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$month])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->sum('total_amount');
    }

    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
