@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Budget</h4>
        <p class="text-muted small mb-0">Update budget: {{ $budget->name }}</p>
    </div>
    <x-button href="{{ route('budgets.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('budgets.update', $budget->id) }}" id="budgetForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <x-input name="name" label="Budget Name" required value="{{ old('name', $budget->name) }}" />
            </div>
            <div class="col-md-6">
                <x-select name="type" label="Budget Type" required :options="['monthly' => 'Monthly', 'yearly' => 'Yearly', 'project' => 'Project']" value="{{ old('type', $budget->type) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="budget_year" type="number" label="Budget Year" required value="{{ old('budget_year', $budget->budget_year) }}" min="2000" max="2100" />
            </div>
            <div class="col-md-4" id="monthField">
                <x-select name="budget_month" label="Month" :options="[
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ]" value="{{ old('budget_month', $budget->budget_month) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="category" label="Category" value="{{ old('category', $budget->category) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="allocated_amount" type="number" step="0.01" label="Allocated Amount" required value="{{ old('allocated_amount', $budget->allocated_amount) }}" />
                <div class="form-text text-muted">Spent: {{ number_format($budget->spent_amount, 2) }} | Remaining: {{ number_format($budget->remaining_amount, 2) }}</div>
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', $budget->status) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input type="date" name="start_date" label="Start Date" required value="{{ old('start_date', $budget->start_date->toDateString()) }}" />
            </div>
            <div class="col-md-6">
                <x-input type="date" name="end_date" label="End Date" required value="{{ old('end_date', $budget->end_date->toDateString()) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description', $budget->description) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="submit" variant="primary" icon="check-lg">Update Budget</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function toggleMonthField() {
        const type = $('select[name="type"]').val();
        if (type === 'monthly') {
            $('#monthField').show();
        } else {
            $('#monthField').hide();
        }
    }

    $('select[name="type"]').on('change', toggleMonthField);
    toggleMonthField();

    $('#budgetForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
    });
});
</script>
@endpush
