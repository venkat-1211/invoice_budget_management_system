@extends('layouts.app')

@section('title', 'Budget Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Budget Report</h4>
        <p class="text-muted small mb-0">Financial year {{ $report['year'] }}</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2000" max="2100">
        <x-button type="submit" variant="primary" size="sm" icon="filter">Filter</x-button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <x-card class="stat-card primary h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Allocated</div>
                <h4 class="fw-bold mb-0">{{ number_format($report['summary']['total_allocated'], 2) }}</h4>
            </div>
        </x-card>
    </div>
    <div class="col-md-3">
        <x-card class="stat-card danger h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Spent</div>
                <h4 class="fw-bold mb-0">{{ number_format($report['summary']['total_spent'], 2) }}</h4>
            </div>
        </x-card>
    </div>
    <div class="col-md-3">
        <x-card class="stat-card success h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Total Remaining</div>
                <h4 class="fw-bold mb-0">{{ number_format($report['summary']['total_remaining'], 2) }}</h4>
            </div>
        </x-card>
    </div>
    <div class="col-md-3">
        <x-card class="stat-card warning h-100">
            <div class="card-body">
                <div class="text-uppercase text-muted small fw-bold mb-1">Over Budget</div>
                <h4 class="fw-bold mb-0">{{ $report['summary']['over_budget_count'] }}</h4>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <x-card title="Monthly Breakdown">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Allocated</th>
                            <th class="text-end">Spent</th>
                            <th class="text-end">Remaining</th>
                            <th class="text-center">Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        @endphp
                        @for($i = 1; $i <= 12; $i++)
                        @php
                            $monthly = $report['monthly_breakdown'][$i];
                            $pct = $monthly['allocated'] > 0 ? ($monthly['spent'] / $monthly['allocated'] * 100) : 0;
                            $barColor = $pct > 100 ? 'bg-danger' : ($pct > 80 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $months[$i-1] }}</td>
                            <td class="text-end">{{ number_format($monthly['allocated'], 2) }}</td>
                            <td class="text-end">{{ number_format($monthly['spent'], 2) }}</td>
                            <td class="text-end {{ $monthly['allocated'] - $monthly['spent'] < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($monthly['allocated'] - $monthly['spent'], 2) }}
                            </td>
                            <td class="text-center" style="width: 200px;">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ min(100, $pct) }}%">
                                        {{ number_format($pct, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <x-card title="Budget Details">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th class="text-end">Allocated</th>
                            <th class="text-end">Spent</th>
                            <th class="text-end">Remaining</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['budgets'] as $budget)
                        <tr>
                            <td class="fw-semibold">{{ $budget->name }}</td>
                            <td>{{ ucfirst($budget->type) }}</td>
                            <td>{{ $budget->category ?? '—' }}</td>
                            <td class="text-end">{{ number_format($budget->allocated_amount, 2) }}</td>
                            <td class="text-end {{ $budget->spent_amount > $budget->allocated_amount ? 'text-danger' : '' }}">
                                {{ number_format($budget->spent_amount, 2) }}
                            </td>
                            <td class="text-end {{ $budget->remaining_amount < 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                {{ number_format($budget->remaining_amount, 2) }}
                            </td>
                            <td class="text-center">
                                <x-badge variant="{{ $budget->status == 1 ? 'success' : 'secondary' }}" pill>
                                    {{ $budget->status == 1 ? 'Active' : 'Inactive' }}
                                </x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No budgets found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>
@endsection
