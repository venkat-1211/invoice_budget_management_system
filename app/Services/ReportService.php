<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getRevenueReport(string $startDate, string $endDate): array
    {
        $cacheKey = "revenue_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
            $daily = DB::table('sales_invoices')
                ->selectRaw('DATE(invoice_date) as date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $byCustomer = DB::table('sales_invoices')
                ->selectRaw('customers.name as customer, COUNT(*) as count, SUM(sales_invoices.total_amount) as total')
                ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
                ->where('sales_invoices.status', 1)
                ->whereNull('sales_invoices.deleted_at')
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total')
                ->get();

            return [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'summary' => [
                    'total_revenue' => $daily->sum('total'),
                    'total_invoices' => $daily->sum('count'),
                    'total_paid' => $daily->sum('paid'),
                    'total_pending' => $daily->sum('total') - $daily->sum('paid'),
                ],
                'daily_breakdown' => $daily,
                'by_customer' => $byCustomer,
            ];
        });
    }

    public function getExpenseReport(string $startDate, string $endDate): array
    {
        $cacheKey = "expense_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
            $daily = DB::table('expenses')
                ->selectRaw('DATE(expense_date) as date, COUNT(*) as count, SUM(total_amount) as total')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $byCategory = DB::table('expenses')
                ->selectRaw('category, COUNT(*) as count, SUM(total_amount) as total')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();

            $byBudget = DB::table('expenses')
                ->selectRaw('budgets.name as budget, COUNT(*) as count, SUM(expenses.total_amount) as total')
                ->leftJoin('budgets', 'expenses.budget_id', '=', 'budgets.id')
                ->whereBetween('expenses.expense_date', [$startDate, $endDate])
                ->where('expenses.status', 1)
                ->whereNull('expenses.deleted_at')
                ->groupBy('budgets.id', 'budgets.name')
                ->orderByDesc('total')
                ->get();

            return [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'summary' => [
                    'total_expenses' => $daily->sum('total'),
                    'total_count' => $daily->sum('count'),
                ],
                'daily_breakdown' => $daily,
                'by_category' => $byCategory,
                'by_budget' => $byBudget,
            ];
        });
    }

    public function getBudgetReport(int $year): array
    {
        $cacheKey = "budget_report_{$year}";

        return Cache::remember($cacheKey, 3600, function () use ($year) {
            $budgets = DB::table('budgets')
                ->where('budget_year', $year)
                ->whereNull('deleted_at')
                ->get();

            $monthly = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthly[$i] = [
                    'allocated' => (float) $budgets->where('budget_month', $i)->sum('allocated_amount'),
                    'spent' => (float) $budgets->where('budget_month', $i)->sum('spent_amount'),
                ];
            }

            return [
                'year' => $year,
                'summary' => [
                    'total_allocated' => $budgets->sum('allocated_amount'),
                    'total_spent' => $budgets->sum('spent_amount'),
                    'total_remaining' => $budgets->sum('remaining_amount'),
                    'over_budget_count' => $budgets->filter(fn($b) => $b->spent_amount > $b->allocated_amount)->count(),
                ],
                'budgets' => $budgets,
                'monthly_breakdown' => $monthly,
            ];
        });
    }

    public function getProfitReport(string $startDate, string $endDate): array
    {
        $cacheKey = "profit_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
            $revenue = (float) DB::table('sales_invoices')
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->sum('total_amount');

            $cogs = (float) DB::table('purchase_invoices')
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->sum('total_amount');

            $expenses = (float) DB::table('expenses')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->sum('total_amount');

            $grossProfit = $revenue - $cogs;
            $netProfit = $grossProfit - $expenses;

            return [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'revenue' => $revenue,
                'cost_of_goods_sold' => $cogs,
                'gross_profit' => $grossProfit,
                'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
                'operating_expenses' => $expenses,
                'net_profit' => $netProfit,
                'net_margin' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0,
            ];
        });
    }
}
