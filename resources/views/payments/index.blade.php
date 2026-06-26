@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Payments</h4>
        <p class="text-muted small mb-0">Manage invoice payments and transactions</p>
    </div>
    <x-button href="{{ route('payments.create') }}" variant="success" icon="plus-lg">
        Record Payment
    </x-button>
</div>

@php
use Illuminate\Support\HtmlString;

$columns = [
    ['data' => 'payment_number', 'title' => 'Payment #'],

    ['data' => 'payment_date', 'title' => 'Date', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'invoice_ref', 'title' => 'Invoice', 'render' => new HtmlString(
        'function(data) { return data ? data : \'<span class="text-muted">—</span>\'; }'
    )],

    ['data' => 'amount', 'title' => 'Amount', 'render' => new HtmlString(
        'function(data) { return window.formatCurrency(data); }'
    )],

    ['data' => 'payment_method', 'title' => 'Method', 'render' => new HtmlString(
        'function(data) {
            return data.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());
        }'
    )],

    ['data' => 'reference', 'title' => 'Reference', 'render' => new HtmlString(
        'function(data) { return data ? data : \'<span class="text-muted">—</span>\'; }'
    )],

    ['data' => 'created_at', 'title' => 'Recorded', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => new HtmlString(
        'function(data, type, row) {
            return `<button onclick="deletePayment(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="payments-table"
        :ajaxUrl="route('payments.datatable')"
        :columns="$columns"
        :orderColumn="1"
        orderDirection="desc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this payment? This will reverse the invoice payment status.</p>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteForm" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </x-slot:footer>
</x-modal>
@endsection

@push('scripts')
<script>
function deletePayment(id) {
    $('#deleteForm').attr('action', `/payments/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
