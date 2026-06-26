@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Add Product</h4>
        <p class="text-muted small mb-0">Create a new product or service</p>
    </div>
    <x-button href="{{ route('products.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('products.store') }}" id="productForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-input name="sku" label="SKU" required value="{{ old('sku', 'SKU-' . strtoupper(substr(uniqid(), -6))) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Product Name" required value="{{ old('name') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-select name="type" label="Type" :options="['product' => 'Product', 'service' => 'Service']" value="{{ old('type', 'product') }}" />
            </div>
            <div class="col-md-6">
                <x-input name="category" label="Category" value="{{ old('category') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="unit" label="Unit" value="{{ old('unit', 'pcs') }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="purchase_price" label="Purchase Price" required value="{{ old('purchase_price', 0) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="sale_price" label="Sale Price" required value="{{ old('sale_price', 0) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="tax_rate" label="Tax Rate (%)" value="{{ old('tax_rate', 0) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" name="stock_quantity" label="Stock Quantity" value="{{ old('stock_quantity', 0) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" name="min_stock_level" label="Min Stock Level" value="{{ old('min_stock_level', 0) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', 1) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Save Product</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#productForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush
