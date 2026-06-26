<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ExchangeRate\ExchangeRateServiceInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private ExchangeRateServiceInterface $exchangeRateService
    ) {}

    public function index(Request $request)
    {
        $summary = $this->dashboardService->getSummaryCards();
        $revenueChart = $this->dashboardService->getRevenueChartData();
        $expenseChart = $this->dashboardService->getExpenseChartData();
        $budgetUsage = $this->dashboardService->getBudgetUsageData();
        $recentActivity = $this->dashboardService->getRecentActivity();

        $exchangeRates = [];
        try {
            $rates = $this->exchangeRateService->fetchLatestRates('USD');
            $currencies = ['EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'];
            foreach ($currencies as $curr) {
                if (isset($rates[$curr])) {
                    $exchangeRates[$curr] = $rates[$curr];
                }
            }
        } catch (\Exception $e) {
            $exchangeRates = [];
        }

        return view('dashboard.index', compact(
            'summary', 'revenueChart', 'expenseChart',
            'budgetUsage', 'recentActivity', 'exchangeRates'
        ));
    }
}
