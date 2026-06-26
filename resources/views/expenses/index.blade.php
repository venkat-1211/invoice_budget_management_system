@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Expenses</h4>
        <p class="text-muted small mb-0">Track and manage business expenses</p>
    </div>
    <x-button href="{{ route('expenses.create') }}" variant="primary" icon="plus-lg">
        Record Expense
    </x-button>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-primary btn-sm {{ !request('category') ? 'active' : '' }}">All</a>
            <a href="{{ route('expenses.index', ['category' => 'Office']) }}" class="btn btn-outline-secondary btn-sm {{ request('category') === 'Office' ? 'active' : '' }}">Office</a>
            <a href="{{ route('expenses.index', ['category' => 'Travel']) }}" class="btn btn-outline-secondary btn-sm {{ request('category') === 'Travel' ? 'active' : '' }}">Travel</a>
            <a href="{{ route('expenses.index', ['category' => 'Utilities']) }}" class="btn btn-outline-secondary btn-sm {{ request('category') === 'Utilities' ? 'active' : '' }}">Utilities</a>
        </div>
    </div>
</div>

@php
use Illuminate\Support\HtmlString;

$columns = [
    ['data' => 'expense_number', 'title' => 'Expense #'],

    ['data' => 'expense_date', 'title' => 'Date', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'category', 'title' => 'Category'],

    ['data' => 'payment_method', 'title' => 'Payment', 'render' => new HtmlString(
        'function(data) {
            return data.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());
        }'
    )],

    ['data' => 'total_amount', 'title' => 'Amount', 'render' => new HtmlString(
        'function(data, type, row) {
            return window.formatCurrency(data, row.currency);
        }'
    )],

    ['data' => 'budget_name', 'title' => 'Budget', 'render' => new HtmlString(
        'function(data) {
            return data ? data : \'<span class="text-muted">—</span>\';
        }'
    )],

    ['data' => 'status', 'title' => 'Status', 'orderable' => false, 'render' => new HtmlString(
        'function(data) {
            return data == 1
                ? \'<span class="badge bg-success">Active</span>\'
                : \'<span class="badge bg-secondary">Inactive</span>\';
        }'
    )],

    ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => new HtmlString(
        'function(data, type, row) {
            return `<a href="/expenses/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteExpense(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="expenses-table"
        :ajaxUrl="route('expenses.datatable')"
        :columns="$columns"
        :orderColumn="1"
        orderDirection="desc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this expense?</p>
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
function deleteExpense(id) {
    $('#deleteForm').attr('action', `/expenses/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
