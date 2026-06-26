@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Expense</h4>
        <p class="text-muted small mb-0">Update expense record: {{ $expense->expense_number }}</p>
    </div>
    <x-button href="{{ route('expenses.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('expenses.update', $expense->id) }}" id="expenseForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <x-select name="budget_id" label="Link to Budget" :options="[$expense->budget_id => $expense->budget->name ?? '']" value="{{ $expense->budget_id }}" placeholder="Select Budget (Optional)" />
            </div>
            <div class="col-md-6">
                <x-select name="supplier_id" label="Supplier" :options="[$expense->supplier_id => $expense->supplier->name ?? '']" value="{{ $expense->supplier_id }}" placeholder="Select Supplier (Optional)" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input type="date" name="expense_date" label="Expense Date" required value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" />
            </div>
            <div class="col-md-4">
                <x-select name="category" label="Category" required :options="[
                    'Office' => 'Office Supplies',
                    'Travel' => 'Travel & Transport',
                    'Utilities' => 'Utilities',
                    'Marketing' => 'Marketing',
                    'Salaries' => 'Salaries',
                    'Rent' => 'Rent',
                    'Maintenance' => 'Maintenance',
                    'Other' => 'Other'
                ]" value="{{ old('category', $expense->category) }}" />
            </div>
            <div class="col-md-4">
                <x-select name="payment_method" label="Payment Method" required :options="[
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'check' => 'Check'
                ]" value="{{ old('payment_method', $expense->payment_method) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="amount" type="number" step="0.01" label="Amount" required value="{{ old('amount', $expense->amount) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="tax_amount" type="number" step="0.01" label="Tax Amount" value="{{ old('tax_amount', $expense->tax_amount) }}" />
            </div>
            <div class="col-md-4">
                <x-select name="currency" label="Currency" :options="['USD' => 'USD - US Dollar', 'EUR' => 'EUR - Euro', 'GBP' => 'GBP - British Pound']" value="{{ old('currency', $expense->currency) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="reference" label="Reference" value="{{ old('reference', $expense->reference) }}" />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', $expense->status) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description', $expense->description) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="submit" variant="primary" icon="check-lg">Update Expense</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('select[name="budget_id"]').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search budget...',
        allowClear: true,
        ajax: {
            url: '{{ route("budgets.datatable") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, status: 1 };
            },
            processResults: function(data) {
                return {
                    results: data.data ? data.data.map(b => ({ id: b.id, text: b.name + ' (' + b.allocated_amount + ')' })) : []
                };
            }
        }
    });

    $('select[name="supplier_id"]').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search supplier...',
        allowClear: true,
        ajax: {
            url: '{{ route("suppliers.search") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            }
        }
    });

    $('#expenseForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
    });
});
</script>
@endpush
