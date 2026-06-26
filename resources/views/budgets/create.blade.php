@extends('layouts.app')

@section('title', 'Create Budget')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Create Budget</h4>
        <p class="text-muted small mb-0">Set up a new financial budget</p>
    </div>
    <x-button href="{{ route('budgets.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('budgets.store') }}" id="budgetForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-input name="name" label="Budget Name" required value="{{ old('name') }}" placeholder="e.g., Q1 Marketing Budget" />
            </div>
            <div class="col-md-6">
                <x-select name="type" label="Budget Type" required :options="['monthly' => 'Monthly', 'yearly' => 'Yearly', 'project' => 'Project']" value="{{ old('type', 'monthly') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="budget_year" type="number" label="Budget Year" required value="{{ old('budget_year', now()->year) }}" min="2000" max="2100" />
            </div>
            <div class="col-md-4" id="monthField">
                <x-select name="budget_month" label="Month" :options="[
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ]" value="{{ old('budget_month', now()->month) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="category" label="Category" value="{{ old('category') }}" placeholder="e.g., Marketing, Operations" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="allocated_amount" type="number" step="0.01" label="Allocated Amount" required value="{{ old('allocated_amount') }}" />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', 1) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input type="date" name="start_date" label="Start Date" required value="{{ old('start_date', now()->startOfMonth()->toDateString()) }}" />
            </div>
            <div class="col-md-6">
                <x-input type="date" name="end_date" label="End Date" required value="{{ old('end_date', now()->endOfMonth()->toDateString()) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description') }}" placeholder="Enter budget details and purpose..." />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Create Budget</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle month field based on budget type
    function toggleMonthField() {
        const type = $('select[name="type"]').val();
        if (type === 'monthly') {
            $('#monthField').show();
            $('select[name="budget_month"]').prop('required', true);
        } else {
            $('#monthField').hide();
            $('select[name="budget_month"]').prop('required', false);
        }
    }

    $('select[name="type"]').on('change', toggleMonthField);
    toggleMonthField();

    // Auto-set dates based on type and month/year
    $('select[name="type"], select[name="budget_month"], input[name="budget_year"]').on('change', function() {
        const type = $('select[name="type"]').val();
        const year = parseInt($('input[name="budget_year"]').val()) || new Date().getFullYear();
        const month = parseInt($('select[name="budget_month"]').val()) || 1;

        if (type === 'monthly') {
            const startDate = new Date(year, month - 1, 1);
            const endDate = new Date(year, month, 0);
            $('input[name="start_date"]').val(startDate.toISOString().split('T')[0]);
            $('input[name="end_date"]').val(endDate.toISOString().split('T')[0]);
        } else if (type === 'yearly') {
            $('input[name="start_date"]').val(`${year}-01-01`);
            $('input[name="end_date"]').val(`${year}-12-31`);
        }
    });

    $('#budgetForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');
    });
});
</script>
@endpush
