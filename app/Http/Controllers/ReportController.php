<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function revenue(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $report = $this->reportService->getRevenueReport($startDate, $endDate);

        return view('reports.revenue', compact('report', 'startDate', 'endDate'));
    }

    public function expenses(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $report = $this->reportService->getExpenseReport($startDate, $endDate);

        return view('reports.expenses', compact('report', 'startDate', 'endDate'));
    }

    public function budgets(Request $request): View
    {
        $year = $request->get('year', now()->year);

        $report = $this->reportService->getBudgetReport((int)$year);

        return view('reports.budgets', compact('report', 'year'));
    }

    public function profit(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $report = $this->reportService->getProfitReport($startDate, $endDate);

        return view('reports.profit', compact('report', 'startDate', 'endDate'));
    }
}
