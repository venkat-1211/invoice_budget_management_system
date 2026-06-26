@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Product</h4>
        <p class="text-muted small mb-0">Update product information</p>
    </div>
    <x-button href="{{ route('products.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('products.update', $product->id) }}" id="productForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <x-input name="sku" label="SKU" required value="{{ old('sku', $product->sku) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Product Name" required value="{{ old('name', $product->name) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-select name="type" label="Type" :options="['product' => 'Product', 'service' => 'Service']" value="{{ old('type', $product->type) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="category" label="Category" value="{{ old('category', $product->category) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input name="unit" label="Unit" value="{{ old('unit', $product->unit) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="purchase_price" label="Purchase Price" required value="{{ old('purchase_price', $product->purchase_price) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="sale_price" label="Sale Price" required value="{{ old('sale_price', $product->sale_price) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-input type="number" step="0.01" name="tax_rate" label="Tax Rate (%)" value="{{ old('tax_rate', $product->tax_rate) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" name="stock_quantity" label="Stock Quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="number" name="min_stock_level" label="Min Stock Level" value="{{ old('min_stock_level', $product->min_stock_level) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-textarea name="description" label="Description" rows="3" value="{{ old('description', $product->description) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', $product->status) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="submit" variant="primary" icon="check-lg">Update Product</x-button>
        </div>
    </form>
</x-card>
@endsection
