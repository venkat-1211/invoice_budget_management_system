@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Supplier</h4>
        <p class="text-muted small mb-0">Update supplier information</p>
    </div>
    <x-button href="{{ route('suppliers.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" id="supplierForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <x-input name="code" label="Supplier Code" required value="{{ old('code', $supplier->code) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Supplier Name" required value="{{ old('name', $supplier->name) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input type="email" name="email" label="Email Address" value="{{ old('email', $supplier->email) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="phone" label="Phone Number" value="{{ old('phone', $supplier->phone) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="tax_number" label="Tax Number" value="{{ old('tax_number', $supplier->tax_number) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="payment_terms" label="Payment Terms" value="{{ old('payment_terms', $supplier->payment_terms) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="city" label="City" value="{{ old('city', $supplier->city) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="country" label="Country" value="{{ old('country', $supplier->country) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="postal_code" label="Postal Code" value="{{ old('postal_code', $supplier->postal_code) }}" />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', $supplier->status) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <x-textarea name="address" label="Address" rows="3" value="{{ old('address', $supplier->address) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="submit" variant="primary" icon="check-lg">Update Supplier</x-button>
        </div>
    </form>
</x-card>
@endsection
