@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Add Supplier</h4>
        <p class="text-muted small mb-0">Create a new supplier record</p>
    </div>
    <x-button href="{{ route('suppliers.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('suppliers.store') }}" id="supplierForm">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-input name="code" label="Supplier Code" required value="{{ old('code', 'SUP-' . time()) }}" />
            </div>
            <div class="col-md-6">
                <x-input name="name" label="Supplier Name" required value="{{ old('name') }}" />
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
                <x-input name="payment_terms" label="Payment Terms" value="{{ old('payment_terms', 'Net 30') }}" />
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
            <div class="col-12">
                <x-textarea name="address" label="Address" rows="3" value="{{ old('address') }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <x-button type="reset" variant="outline-secondary">Reset</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Save Supplier</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#supplierForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush
