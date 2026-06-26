@extends('layouts.app')

@section('title', 'Purchase Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Purchase Invoices</h4>
        <p class="text-muted small mb-0">Manage purchase invoices from suppliers</p>
    </div>
    <x-button href="{{ route('purchase-invoices.create') }}" variant="primary" icon="plus-lg">
        Create Invoice
    </x-button>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-primary btn-sm {{ !request('status') ? 'active' : '' }}">All</a>
            <a href="{{ route('purchase-invoices.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm {{ request('status') === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('purchase-invoices.index', ['status' => 'paid']) }}" class="btn btn-outline-success btn-sm {{ request('status') === 'paid' ? 'active' : '' }}">Paid</a>
            <a href="{{ route('purchase-invoices.index', ['status' => 'overdue']) }}" class="btn btn-outline-danger btn-sm {{ request('status') === 'overdue' ? 'active' : '' }}">Overdue</a>
        </div>
    </div>
</div>

@php
use Illuminate\Support\HtmlString;

$columns = [
    ['data' => 'invoice_number', 'title' => 'Invoice #'],
    ['data' => 'supplier_name', 'title' => 'Supplier'],

    ['data' => 'invoice_date', 'title' => 'Date', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'due_date', 'title' => 'Due Date', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'total_amount', 'title' => 'Total', 'render' => new HtmlString(
        'function(data, type, row) { return window.formatCurrency(data, row.currency); }'
    )],

    ['data' => 'payment_status', 'title' => 'Status', 'orderable' => false, 'render' => new HtmlString(
        'function(data) {
            const variants = {paid: "success", pending: "warning", partial: "info", overdue: "danger"};

            return `<span class="badge bg-${variants[data] || "secondary"}">
                        ${data.charAt(0).toUpperCase() + data.slice(1)}
                    </span>`;
        }'
    )],

    ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => new HtmlString(
        'function(data, type, row) {
            return `<a href="/purchase-invoices/${row.id}" class="btn btn-sm btn-outline-info me-1">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="/purchase-invoices/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteInvoice(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="purchase-invoices-table"
        :ajaxUrl="route('purchase-invoices.datatable')"
        :columns="$columns"
        :orderColumn="2"
        orderDirection="desc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this invoice?</p>
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
function deleteInvoice(id) {
    $('#deleteForm').attr('action', `/purchase-invoices/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
