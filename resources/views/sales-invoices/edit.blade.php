@extends('layouts.app')

@section('title', 'Edit Sales Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Sales Invoice</h4>
        <p class="text-muted small mb-0">Update sales invoice {{ $invoice->invoice_number }}</p>
    </div>
    <x-button href="{{ route('sales-invoices.index') }}" variant="outline-secondary" icon="arrow-left">
        Back
    </x-button>
</div>

<x-card>
    <form method="POST" action="{{ route('sales-invoices.update', $invoice->id) }}" id="invoiceForm">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-4">
                <x-select name="customer_id" label="Customer" required :options="[$invoice->customer_id => $invoice->customer->name]" value="{{ $invoice->customer_id }}" />
            </div>
            <div class="col-md-4">
                <x-input type="date" name="invoice_date" label="Invoice Date" required value="{{ old('invoice_date', $invoice->invoice_date->toDateString()) }}" />
            </div>
            <div class="col-md-4">
                <x-input type="date" name="due_date" label="Due Date" required value="{{ old('due_date', $invoice->due_date->toDateString()) }}" />
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <x-select name="currency" label="Currency" :options="['USD' => 'USD - US Dollar', 'EUR' => 'EUR - Euro', 'GBP' => 'GBP - British Pound']" value="{{ old('currency', $invoice->currency) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="reference" label="Reference" value="{{ old('reference', $invoice->reference) }}" />
            </div>
            <div class="col-md-4">
                <x-input name="exchange_rate" type="number" step="0.000001" label="Exchange Rate" value="{{ old('exchange_rate', $invoice->exchange_rate) }}" />
            </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3">Invoice Items</h5>

        <div class="table-responsive">
            <table class="table table-bordered" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%">Product/Description</th>
                        <th style="width: 10%">Qty</th>
                        <th style="width: 10%">Unit</th>
                        <th style="width: 15%">Unit Price</th>
                        <th style="width: 10%">Tax %</th>
                        <th style="width: 10%">Disc %</th>
                        <th style="width: 12%">Total</th>
                        <th style="width: 3%"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @foreach($invoice->items as $index => $item)
                    <tr class="item-row">
                        <td>
                            <input type="hidden" name="items[{{ $index }}][product_id]" class="product-id" value="{{ $item->product_id }}">
                            <input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm item-desc" required placeholder="Description" value="{{ $item->description }}">
                        </td>
                        <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty" value="{{ $item->quantity }}" min="0.01" step="0.01" required></td>
                        <td><input type="text" name="items[{{ $index }}][unit]" class="form-control form-control-sm item-unit" value="{{ $item->unit }}"></td>
                        <td><input type="number" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm item-price" value="{{ $item->unit_price }}" min="0" step="0.01" required></td>
                        <td><input type="number" name="items[{{ $index }}][tax_rate]" class="form-control form-control-sm item-tax" value="{{ $item->tax_rate }}" min="0" max="100" step="0.01"></td>
                        <td><input type="number" name="items[{{ $index }}][discount_percent]" class="form-control form-control-sm item-disc" value="{{ $item->discount_percent }}" min="0" max="100" step="0.01"></td>
                        <td><input type="text" class="form-control form-control-sm item-total" readonly value="{{ number_format($item->total, 2) }}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                                <i class="bi bi-plus-lg me-1"></i>Add Item
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-end fw-semibold">Subtotal:</td>
                        <td class="text-end" style="width: 150px;"><span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-end fw-semibold">Tax:</td>
                        <td class="text-end"><span id="taxTotal">{{ number_format($invoice->tax_amount, 2) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-end fw-semibold">Discount:</td>
                        <td class="text-end"><span id="discountTotal">{{ number_format($invoice->discount_amount, 2) }}</span></td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-end fw-bold h5">Total:</td>
                        <td class="text-end fw-bold h5"><span id="grandTotal">{{ number_format($invoice->total_amount, 2) }}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <x-textarea name="notes" label="Notes" rows="3" value="{{ old('notes', $invoice->notes) }}" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <x-button type="button" variant="outline-secondary" onclick="window.history.back()">Cancel</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Update Invoice</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = {{ $invoice->items->count() }};

    // Initialize customer select2
    $('select[name="customer_id"]').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search customer...',
        ajax: {
            url: '{{ route("customers.search") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            }
        }
    });

    // Add item row
    $('#addItem').on('click', function() {
        const newRow = `
            <tr class="item-row">
                <td>
                    <input type="hidden" name="items[${itemIndex}][product_id]" class="product-id">
                    <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm item-desc" required placeholder="Description">
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
                <td><input type="text" name="items[${itemIndex}][unit]" class="form-control form-control-sm item-unit" value="pcs"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm item-price" value="0" min="0" step="0.01" required></td>
                <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control form-control-sm item-tax" value="0" min="0" max="100" step="0.01"></td>
                <td><input type="number" name="items[${itemIndex}][discount_percent]" class="form-control form-control-sm item-disc" value="0" min="0" max="100" step="0.01"></td>
                <td><input type="text" class="form-control form-control-sm item-total" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#itemsBody').append(newRow);
        itemIndex++;
        calculateTotals();
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    // Calculate row total
    $(document).on('input', '.item-qty, .item-price, .item-tax, .item-disc', function() {
        const row = $(this).closest('tr');
        calculateRowTotal(row);
        calculateTotals();
    });

    function calculateRowTotal(row) {
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const price = parseFloat(row.find('.item-price').val()) || 0;
        const taxRate = parseFloat(row.find('.item-tax').val()) || 0;
        const discRate = parseFloat(row.find('.item-disc').val()) || 0;

        const baseAmount = qty * price;
        const taxAmount = baseAmount * (taxRate / 100);
        const discAmount = baseAmount * (discRate / 100);
        const total = baseAmount + taxAmount - discAmount;

        row.find('.item-total').val(total.toFixed(2));
        return { baseAmount, taxAmount, discAmount };
    }

    function calculateTotals() {
        let subtotal = 0, taxTotal = 0, discTotal = 0;

        $('.item-row').each(function() {
            const row = $(this);
            const amounts = calculateRowTotal(row);
            subtotal += amounts.baseAmount;
            taxTotal += amounts.taxAmount;
            discTotal += amounts.discAmount;
        });

        const grandTotal = subtotal + taxTotal - discTotal;

        $('#subtotal').text(subtotal.toFixed(2));
        $('#taxTotal').text(taxTotal.toFixed(2));
        $('#discountTotal').text(discTotal.toFixed(2));
        $('#grandTotal').text(grandTotal.toFixed(2));
    }

    // Initial calculation
    calculateTotals();

    // Form submission
    $('#invoiceForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
    });
});
</script>
@endpush
