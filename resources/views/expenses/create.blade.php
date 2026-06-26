@extends('layouts.app')

@section('title', 'Record Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Record Expense</h4>
        <p class="text-muted small mb-0">Log a new business expense</p>
    </div>
    <x-button href="{{ route('expenses.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('expenses.store') }}" id="expenseForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-select name="budget_id" label="Link to Budget" :options="[]" placeholder="Select Budget (Optional)" />
                <div class="form-text text-muted">Linking to a budget enables automatic tracking</div>
            </div>
            <div class="col-md-6">
                <x-select name="supplier_id" label="Supplier" :options="[]" placeholder="Select Supplier (Optional)" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input type="date" name="expense_date" label="Expense Date" required value="{{ old('expense_date', now()->toDateString()) }}" />
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
                ]" value="{{ old('category') }}" />
            </div>
            <div class="col-md-4">
                <x-select name="payment_method" label="Payment Method" required :options="[
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'check' => 'Check'
                ]" value="{{ old('payment_method', 'cash') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="amount" type="number" step="0.01" label="Amount" required value="{{ old('amount') }}" />
            </div>
            <div class="col-md-4">
                <x-input name="tax_amount" type="number" step="0.01" label="Tax Amount" value="{{ old('tax_amount', 0) }}" />
            </div>
            <div class="col-md-4">
                <x-select name="currency" label="Currency" :options="['USD' => 'USD - US Dollar', 'EUR' => 'EUR - Euro', 'GBP' => 'GBP - British Pound']" value="USD" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="reference" label="Reference" value="{{ old('reference') }}" placeholder="Receipt number, invoice ref, etc." />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', 1) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description') }}" placeholder="Enter expense details..." />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Record Expense</x-button>
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
    // Initialize budget select2
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

    // Initialize supplier select2
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

    // Auto-calculate total on amount/tax change
    $('input[name="amount"], input[name="tax_amount"]').on('input', function() {
        const amount = parseFloat($('input[name="amount"]').val()) || 0;
        const tax = parseFloat($('input[name="tax_amount"]').val()) || 0;
        // Just for display - actual total is calculated server-side
    });

    $('#expenseForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush
