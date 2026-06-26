@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Products & Services</h4>
        <p class="text-muted small mb-0">Manage your product catalog</p>
    </div>
    <x-button href="{{ route('products.create') }}" variant="primary" icon="plus-lg">
        Add Product
    </x-button>
</div>

@php
use Illuminate\Support\HtmlString;

$columns = [
    ['data' => 'sku', 'title' => 'SKU'],
    ['data' => 'name', 'title' => 'Name'],

    ['data' => 'type', 'title' => 'Type', 'render' => new HtmlString(
        'function(data) { return data.charAt(0).toUpperCase() + data.slice(1); }'
    )],

    ['data' => 'category', 'title' => 'Category'],

    ['data' => 'sale_price', 'title' => 'Sale Price', 'render' => new HtmlString(
        'function(data) { return window.formatCurrency(data); }'
    )],

    ['data' => 'stock_quantity', 'title' => 'Stock', 'render' => new HtmlString(
        'function(data, type, row) {
            const isLow = row.stock_quantity <= row.min_stock_level;
            return `<span class="${isLow ? "text-danger fw-bold" : ""}">${data}</span>`;
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
            return `<a href="/products/${row.id}/edit" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="deleteProduct(${row.id})" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }'
    )],
];
@endphp

<x-card>
    <x-datatable
        id="products-table"
        :ajaxUrl="route('products.datatable')"
        :columns="$columns"
        :orderColumn="0"
        orderDirection="asc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this product?</p>
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
function deleteProduct(id) {
    $('#deleteForm').attr('action', `/products/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
