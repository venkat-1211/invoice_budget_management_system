@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Customer</h4>
        <p class="text-muted small mb-0">Update customer information</p>
    </div>
    <x-button href="{{ route('customers.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('customers.update', $customer->id) }}" id="customerForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <x-input name="code" label="Customer Code" required value="{{ old('code', $customer->code) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Customer Name" required value="{{ old('name', $customer->name) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input type="email" name="email" label="Email Address" value="{{ old('email', $customer->email) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="phone" label="Phone Number" value="{{ old('phone', $customer->phone) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="tax_number" label="Tax Number" value="{{ old('tax_number', $customer->tax_number) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="credit_limit" type="number" step="0.01" label="Credit Limit" value="{{ old('credit_limit', $customer->credit_limit) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="city" label="City" value="{{ old('city', $customer->city) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="country" label="Country" value="{{ old('country', $customer->country) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="postal_code" label="Postal Code" value="{{ old('postal_code', $customer->postal_code) }}" />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', $customer->status) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-textarea name="billing_address" label="Billing Address" rows="3" value="{{ old('billing_address', $customer->billing_address) }}" />
            </div>
            <div class="col-md-6">
                <x-textarea name="shipping_address" label="Shipping Address" rows="3" value="{{ old('shipping_address', $customer->shipping_address) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="submit" variant="primary" icon="check-lg">Update Customer</x-button>
        </div>
    </form>
</x-card>
@endsection
