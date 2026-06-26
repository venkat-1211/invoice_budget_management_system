@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Dashboard Overview</h4>
    <div class="text-muted small">{{ now()->format('l, F j, Y') }}</div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card primary shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Total Revenue</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_revenue'], 2) }}</h4>
                        <small class="{{ $summary['revenue_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ $summary['revenue_change'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($summary['revenue_change']) }}% vs last month
                        </small>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                        <i class="bi bi-currency-dollar text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card danger shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Total Expenses</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_expenses'], 2) }}</h4>
                        <small class="{{ $summary['expense_change'] >= 0 ? 'text-danger' : 'text-success' }}">
                            <i class="bi bi-arrow-{{ $summary['expense_change'] >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($summary['expense_change']) }}% vs last month
                        </small>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-2 rounded">
                        <i class="bi bi-cash-stack text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card warning shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Pending Invoices</div>
                        <h4 class="fw-bold mb-0">{{ $summary['pending_invoices'] }}</h4>
                        <small class="text-muted">{{ $summary['overdue_invoices'] }} overdue</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-2 rounded">
                        <i class="bi bi-clock-history text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card success shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Total Customers</div>
                        <h4 class="fw-bold mb-0">{{ $summary['total_customers'] }}</h4>
                        <small class="text-muted">{{ $summary['total_suppliers'] }} suppliers</small>
                    </div>
                    <div class="bg-success bg-opacity-10 p-2 rounded">
                        <i class="bi bi-people text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <x-card title="Revenue vs Expenses" subtitle="Last 6 months comparison">
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Exchange Rates -->
    <div class="col-lg-4">
        <x-card title="Exchange Rates" subtitle="Base: USD">
            <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Currency</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exchangeRates as $currency => $rate)
                        <tr>
                            <td class="fw-semibold">{{ $currency }}</td>
                            <td class="text-end">{{ number_format($rate, 4) }}</td>
                            <td class="text-end">
                                <span class="badge bg-success bg-opacity-10 text-success">Live</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Rates unavailable</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Budget Usage -->
    <div class="col-lg-6">
        <x-card title="Budget Utilization" subtitle="Current active budgets">
            <div class="chart-container" style="height: 250px;">
                <canvas id="budgetChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <x-card title="Recent Activity" subtitle="Latest transactions">
            <ul class="list-group list-group-flush">
                @forelse($recentActivity['recent_invoices'] as $invoice)
                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold small">{{ $invoice->invoice_number }}</div>
                        <div class="text-muted smaller">{{ $invoice->customer_name ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold small">{{ number_format($invoice->total_amount, 2) }}</div>
                        <x-badge variant="{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'overdue' ? 'danger' : 'warning') }}" pill>
                            {{ ucfirst($invoice->payment_status) }}
                        </x-badge>
                    </div>
                </li>
                @empty
                <li class="list-group-item px-0 text-muted text-center">No recent invoices</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>

<!-- Expense Breakdown -->
<div class="row g-3">
    <div class="col-12">
        <x-card title="Expense Trends" subtitle="Monthly expense analysis">
            <div class="chart-container">
                <canvas id="expenseChart"></canvas>
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Revenue vs Expenses Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenueChart['labels']),
            datasets: [{
                label: 'Revenue',
                data: @json($revenueChart['data']),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Expenses',
                data: @json($expenseChart['data']),
                borderColor: '#e74a3b',
                backgroundColor: 'rgba(231, 74, 59, 0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Budget Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: @json($budgetUsage['labels']),
            datasets: [{
                label: 'Allocated',
                data: @json($budgetUsage['allocated']),
                backgroundColor: 'rgba(78, 115, 223, 0.8)'
            }, {
                label: 'Spent',
                data: @json($budgetUsage['spent']),
                backgroundColor: 'rgba(231, 74, 59, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'bar',
        data: {
            labels: @json($expenseChart['labels']),
            datasets: [{
                label: 'Expenses',
                data: @json($expenseChart['data']),
                backgroundColor: 'rgba(54, 185, 204, 0.8)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
