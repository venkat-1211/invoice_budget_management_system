@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Record Payment</h4>
        <p class="text-muted small mb-0">Apply payment to an invoice</p>
    </div>
    <x-button href="{{ route('payments.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-select name="payable_type" label="Invoice Type" required :options="[
                    'App\\Models\\SalesInvoice' => 'Sales Invoice',
                    'App\\Models\\PurchaseInvoice' => 'Purchase Invoice'
                ]" value="{{ old('payable_type', request('type') === 'purchase' ? 'App\\Models\\PurchaseInvoice' : 'App\\Models\\SalesInvoice') }}" />
            </div>
            <div class="col-md-6">
                <x-select name="payable_id" label="Select Invoice" required :options="[]" placeholder="Search invoice..." />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input type="date" name="payment_date" label="Payment Date" required value="{{ old('payment_date', now()->toDateString()) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="amount" type="number" step="0.01" label="Amount" required value="{{ old('amount') }}" />
            </div>
            <div class="col-md-4">
                <x-select name="payment_method" label="Payment Method" required :options="[
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'check' => 'Check',
                    'online' => 'Online Payment'
                ]" value="{{ old('payment_method', 'cash') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="reference" label="Reference" value="{{ old('reference') }}" placeholder="Check number, transaction ID, etc." />
            </div>
            <div class="col-md-6">
                <x-input name="transaction_id" label="Transaction ID" value="{{ old('transaction_id') }}" placeholder="Gateway transaction ID" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="notes" label="Notes" rows="3" value="{{ old('notes') }}" placeholder="Additional payment details..." />
            </div>
        </div>

        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>Payment will automatically update the invoice status and customer/supplier balance.</div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="success" icon="check-lg">Record Payment</x-button>
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
    function loadInvoices() {
        const type = $('select[name="payable_type"]').val();
        const url = type === 'App\\Models\\SalesInvoice'
            ? '{{ route("sales-invoices.datatable") }}'
            : '{{ route("purchase-invoices.datatable") }}';

        $('select[name="payable_id"]').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search invoice...',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        status: 'pending,partial,overdue'
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.data ? data.data.map(inv => ({
                            id: inv.id,
                            text: inv.invoice_number + ' - ' + (inv.customer_name || inv.supplier_name) + ' (' + inv.remaining_amount + ')',
                            remaining: inv.total_amount - inv.paid_amount
                        })) : []
                    };
                }
            }
        });
    }

    loadInvoices();

    $('select[name="payable_type"]').on('change', function() {
        $('select[name="payable_id"]').val(null).trigger('change');
        loadInvoices();
    });

    // Auto-fill amount when invoice selected
    $('select[name="payable_id"]').on('select2:select', function(e) {
        const data = e.params.data;
        if (data.remaining) {
            $('input[name="amount"]').val(data.remaining);
        }
    });

    $('#paymentForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Recording...');
    });
});
</script>
@endpush
