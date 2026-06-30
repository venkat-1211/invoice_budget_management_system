@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Customers</h4>
        <p class="text-muted small mb-0">Manage your customer database</p>
    </div>
    <x-button href="{{ route('customers.create') }}" variant="primary" icon="plus-lg">
        Add Customer
    </x-button>
</div>

@php
use Illuminate\Support\Js;

$columns = [
    ['data' => 'code', 'title' => 'Code'],
    ['data' => 'name', 'title' => 'Name'],
    ['data' => 'email', 'title' => 'Email'],
    ['data' => 'phone', 'title' => 'Phone'],

    ['data' => 'balance', 'title' => 'Balance', 'render' => new \Illuminate\Support\HtmlString('function(data) { return window.formatCurrency(data); }')],

    ['data' => 'status', 'title' => 'Status', 'orderable' => false, 'render' => new \Illuminate\Support\HtmlString(
        'function(data) {
            return data == 1
                ? \'<span class="badge bg-success">Active</span>\'
                : \'<span class="badge bg-secondary">Inactive</span>\';
        }'
    )],

    ['data' => 'created_at', 'title' => 'Created', 'render' => new \Illuminate\Support\HtmlString(
        'function(data) { return window.formatDate(data); }'
    )],

    ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => new \Illuminate\Support\HtmlString(
        'function(data, type, row) {
            return `<a href="/customers/${row.uuid}/edit" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteCustomer(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="customers-table"
        :ajaxUrl="route('customers.datatable')"
        :columns="$columns"
        :orderColumn="0"
        orderDirection="asc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this customer?</p>
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
function deleteCustomer(id) {
    $('#deleteForm').attr('action', `/customers/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
