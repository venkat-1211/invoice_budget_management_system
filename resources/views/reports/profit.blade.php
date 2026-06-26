@extends('layouts.app')

@section('title', 'Profit Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Profit & Loss Report</h4>
        <p class="text-muted small mb-0">{{ $report['period']['start'] }} to {{ $report['period']['end'] }}</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        <x-button type="submit" variant="primary" size="sm" icon="filter">Filter</x-button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-card class="stat-card primary h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Revenue</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['revenue'], 2) }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card danger h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Cost of Goods Sold</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['cost_of_goods_sold'], 2) }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card info h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Operating Expenses</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['operating_expenses'], 2) }}</h3>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <x-card title="Profit Summary" class="h-100">
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Revenue</td>
                            <td class="text-end fw-bold">{{ number_format($report['revenue'], 2) }}</td>
                        </tr>
                        <tr class="text-danger">
                            <td class="fw-semibold">Less: Cost of Goods Sold</td>
                            <td class="text-end fw-bold">({{ number_format($report['cost_of_goods_sold'], 2) }})</td>
                        </tr>
                        <tr class="table-active border-top">
                            <td class="fw-bold">Gross Profit</td>
                            <td class="text-end fw-bold {{ $report['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($report['gross_profit'], 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr class="text-danger">
                            <td class="fw-semibold">Less: Operating Expenses</td>
                            <td class="text-end fw-bold">({{ number_format($report['operating_expenses'], 2) }})</td>
                        </tr>
                        <tr class="table-active border-top border-2">
                            <td class="fw-bold h5">Net Profit</td>
                            <td class="text-end fw-bold h5 {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($report['net_profit'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card title="Margin Analysis" class="h-100">
            <div class="row g-3">
                <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="text-muted small mb-1">Gross Margin</div>
                        <h2 class="fw-bold {{ $report['gross_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $report['gross_margin'] }}%
                        </h2>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar {{ $report['gross_margin'] >= 30 ? 'bg-success' : ($report['gross_margin'] >= 15 ? 'bg-warning' : 'bg-danger') }}"
                                 style="width: {{ min(100, max(0, $report['gross_margin'])) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="text-muted small mb-1">Net Margin</div>
                        <h2 class="fw-bold {{ $report['net_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $report['net_margin'] }}%
                        </h2>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar {{ $report['net_margin'] >= 15 ? 'bg-success' : ($report['net_margin'] >= 5 ? 'bg-warning' : 'bg-danger') }}"
                                 style="width: {{ min(100, max(0, $report['net_margin'])) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h6 class="fw-bold mb-3">Key Metrics</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Revenue per Day</span>
                    <span class="fw-semibold">
                        {{ number_format($report['revenue'] / max(1, \Carbon\Carbon::parse($startDate)->diffInDays($endDate)), 2) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Expense Ratio</span>
                    <span class="fw-semibold">
                        {{ $report['revenue'] > 0 ? number_format(($report['operating_expenses'] / $report['revenue']) * 100, 2) : 0 }}%
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">COGS Ratio</span>
                    <span class="fw-semibold">
                        {{ $report['revenue'] > 0 ? number_format(($report['cost_of_goods_sold'] / $report['revenue']) * 100, 2) : 0 }}%
                    </span>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <x-card title="Performance Indicators">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-3">
                        <i class="bi bi-graph-up-arrow text-success fs-1 mb-2"></i>
                        <h5 class="fw-bold">{{ $report['gross_profit'] >= 0 ? 'Profitable' : 'Loss Making' }}</h5>
                        <p class="text-muted small">Gross profit {{ $report['gross_profit'] >= 0 ? 'positive' : 'negative' }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <i class="bi bi-cash-stack text-primary fs-1 mb-2"></i>
                        <h5 class="fw-bold">{{ $report['revenue'] > 0 ? 'Revenue Generated' : 'No Revenue' }}</h5>
                        <p class="text-muted small">Sales activity {{ $report['revenue'] > 0 ? 'recorded' : 'absent' }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <i class="bi bi-piggy-bank text-warning fs-1 mb-2"></i>
                        <h5 class="fw-bold">{{ $report['net_margin'] > 10 ? 'Healthy Margin' : 'Low Margin' }}</h5>
                        <p class="text-muted small">Net margin at {{ $report['net_margin'] }}%</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <i class="bi bi-shield-check text-info fs-1 mb-2"></i>
                        <h5 class="fw-bold">{{ $report['cost_of_goods_sold'] < $report['revenue'] * 0.6 ? 'Cost Efficient' : 'High COGS' }}</h5>
                        <p class="text-muted small">COGS at {{ $report['revenue'] > 0 ? number_format(($report['cost_of_goods_sold'] / $report['revenue']) * 100, 1) : 0 }}%</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
