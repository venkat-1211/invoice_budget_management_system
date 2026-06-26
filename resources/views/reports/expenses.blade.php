@extends('layouts.app')

@section('title', 'Expense Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Expense Report</h4>
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
        <x-card class="stat-card danger h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Expenses</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['summary']['total_expenses'], 2) }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card info h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Transactions</div>
                <h3 class="fw-bold mb-0">{{ $report['summary']['total_count'] }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card warning h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Average per Day</div>
                <h3 class="fw-bold mb-0">
                    {{ number_format($report['daily_breakdown']->count() > 0 ? $report['summary']['total_expenses'] / $report['daily_breakdown']->count() : 0, 2) }}
                </h3>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <x-card title="Daily Breakdown">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['daily_breakdown'] as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                            <td class="text-center">{{ $day->count }}</td>
                            <td class="text-end fw-semibold">{{ number_format($day->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <div class="col-lg-4">
        <x-card title="By Category">
            <div class="list-group list-group-flush">
                @forelse($report['by_category'] as $category)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">{{ $category->category }}</span>
                    <span class="text-muted">{{ number_format($category->total, 2) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-3">No data available</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="By Budget" class="mt-3">
            <div class="list-group list-group-flush">
                @forelse($report['by_budget'] as $budget)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">{{ $budget->budget ?? 'Unbudgeted' }}</span>
                    <span class="text-muted">{{ number_format($budget->total, 2) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-3">No data available</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection
