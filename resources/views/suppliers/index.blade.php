@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Suppliers</h4>
        <p class="text-muted small mb-0">Manage your supplier database</p>
    </div>
    <x-button href="{{ route('suppliers.create') }}" variant="primary" icon="plus-lg">
        Add Supplier
    </x-button>
</div>

@php
use Illuminate\Support\HtmlString;

$columns = [
    ['data' => 'code', 'title' => 'Code'],
    ['data' => 'name', 'title' => 'Name'],
    ['data' => 'email', 'title' => 'Email'],
    ['data' => 'phone', 'title' => 'Phone'],
    ['data' => 'payment_terms', 'title' => 'Terms'],

    ['data' => 'balance', 'title' => 'Balance', 'render' => new HtmlString(
        'function(data) { return window.formatCurrency(data); }'
    )],

    ['data' => 'status', 'title' => 'Status', 'orderable' => false, 'render' => new HtmlString(
        'function(data) {
            return data == 1
                ? \'<span class="badge bg-success">Active</span>\'
                : \'<span class="badge bg-secondary">Inactive</span>\';
        }'
    )],

    ['data' => 'created_at', 'title' => 'Created', 'render' => new HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => new HtmlString(
        'function(data, type, row) {
            return `<a href="/suppliers/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteSupplier(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="suppliers-table"
        :ajaxUrl="route('suppliers.datatable')"
        :columns="$columns"
        :orderColumn="0"
        orderDirection="asc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this supplier?</p>
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
function deleteSupplier(id) {
    $('#deleteForm').attr('action', `/suppliers/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
