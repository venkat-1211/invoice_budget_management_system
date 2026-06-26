@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Add Customer</h4>
        <p class="text-muted small mb-0">Create a new customer record</p>
    </div>
    <x-button href="{{ route('customers.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('customers.store') }}" id="customerForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-input name="code" label="Customer Code" value="{{ old('code', 'CUST-' . time()) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Customer Name" value="{{ old('name') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input type="email" name="email" label="Email Address" value="{{ old('email') }}" />
            </div>
            <div class="col-md-6">
                <x-input name="phone" label="Phone Number" value="{{ old('phone') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="tax_number" label="Tax Number" value="{{ old('tax_number') }}" />
            </div>
            <div class="col-md-6">
                <x-input name="credit_limit" type="number" step="0.01" label="Credit Limit" value="{{ old('credit_limit', 0) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="city" label="City" value="{{ old('city') }}" />
            </div>
            <div class="col-md-6">
                <x-input name="country" label="Country" value="{{ old('country') }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-input name="postal_code" label="Postal Code" value="{{ old('postal_code') }}" />
            </div>
            <div class="col-md-6">
                <x-select name="status" label="Status" :options="[1 => 'Active', 0 => 'Inactive']" value="{{ old('status', 1) }}" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-textarea name="billing_address" label="Billing Address" rows="3" value="{{ old('billing_address') }}" />
            </div>
            <div class="col-md-6">
                <x-textarea name="shipping_address" label="Shipping Address" rows="3" value="{{ old('shipping_address') }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Save Customer</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#customerForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush
