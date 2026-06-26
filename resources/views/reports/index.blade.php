@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Financial Reports</h4>
        <p class="text-muted small mb-0">Generate and view financial analytics</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <x-card class="h-100 text-center" bodyClass="d-flex flex-column justify-content-center">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-graph-up-arrow text-primary fs-2"></i>
            </div>
            <h5 class="fw-bold">Revenue Report</h5>
            <p class="text-muted small">Analyze sales revenue over time</p>
            <x-button href="{{ route('reports.revenue') }}" variant="outline-primary" class="mt-auto">View Report</x-button>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-3">
        <x-card class="h-100 text-center" bodyClass="d-flex flex-column justify-content-center">
            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-graph-down-arrow text-danger fs-2"></i>
            </div>
            <h5 class="fw-bold">Expense Report</h5>
            <p class="text-muted small">Track and analyze expenses</p>
            <x-button href="{{ route('reports.expenses') }}" variant="outline-danger" class="mt-auto">View Report</x-button>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-3">
        <x-card class="h-100 text-center" bodyClass="d-flex flex-column justify-content-center">
            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-pie-chart text-info fs-2"></i>
            </div>
            <h5 class="fw-bold">Budget Report</h5>
            <p class="text-muted small">Review budget utilization</p>
            <x-button href="{{ route('reports.budgets') }}" variant="outline-info" class="mt-auto">View Report</x-button>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-3">
        <x-card class="h-100 text-center" bodyClass="d-flex flex-column justify-content-center">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-cash-coin text-success fs-2"></i>
            </div>
            <h5 class="fw-bold">Profit Report</h5>
            <p class="text-muted small">Calculate profit and margins</p>
            <x-button href="{{ route('reports.profit') }}" variant="outline-success" class="mt-auto">View Report</x-button>
        </x-card>
    </div>
</div>
@endsection
