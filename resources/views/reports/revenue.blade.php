@extends('layouts.app')

@section('title', 'Revenue Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Revenue Report</h4>
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
                <h3 class="fw-bold mb-0">{{ number_format($report['summary']['total_revenue'], 2) }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card success h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Paid</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['summary']['total_paid'], 2) }}</h3>
            </div>
        </x-card>
    </div>
    <div class="col-md-4">
        <x-card class="stat-card warning h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Pending</div>
                <h3 class="fw-bold mb-0">{{ number_format($report['summary']['total_pending'], 2) }}</h3>
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
                            <th class="text-center">Invoices</th>
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
        <x-card title="By Customer">
            <div class="list-group list-group-flush">
                @forelse($report['by_customer'] as $customer)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small">{{ $customer->customer }}</span>
                    <span class="text-muted">{{ number_format($customer->total, 2) }}</span>
                </div>
                @empty
                <p class="text-muted text-center py-3">No data available</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection
