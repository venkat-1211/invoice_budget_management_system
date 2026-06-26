@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Invoice {{ $invoice->invoice_number }}</h4>
        <p class="text-muted small mb-0">View invoice details</p>
    </div>
    <div class="d-flex gap-2">
        <x-button href="{{ route('sales-invoices.index') }}" variant="outline-secondary" icon="arrow-left">
            Back
        </x-button>
        <x-button href="{{ route('payments.create', ['type' => 'sales', 'invoice_id' => $invoice->id]) }}" variant="success" icon="credit-card">
            Record Payment
        </x-button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <x-card>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold">Bill To</h6>
                    <p class="mb-1 fw-semibold">{{ $invoice->customer->name }}</p>
                    <p class="mb-1 text-muted small">{{ $invoice->customer->email }}</p>
                    <p class="mb-1 text-muted small">{{ $invoice->customer->phone }}</p>
                    <p class="mb-0 text-muted small">{{ $invoice->customer->billing_address }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase small fw-bold">Invoice Details</h6>
                    <p class="mb-1"><span class="text-muted">Date:</span> <span class="fw-semibold">{{ $invoice->invoice_date->format('M d, Y') }}</span></p>
                    <p class="mb-1"><span class="text-muted">Due Date:</span> <span class="fw-semibold">{{ $invoice->due_date->format('M d, Y') }}</span></p>
                    <p class="mb-1"><span class="text-muted">Reference:</span> <span class="fw-semibold">{{ $invoice->reference ?? 'N/A' }}</span></p>
                    <p class="mb-0">
                        <x-badge variant="{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'overdue' ? 'danger' : 'warning') }}" pill>
                            {{ ucfirst($invoice->payment_status) }}
                        </x-badge>
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount_amount, 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">Subtotal:</td>
                            <td colspan="2" class="text-end fw-semibold">{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">Tax:</td>
                            <td colspan="2" class="text-end">{{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">Discount:</td>
                            <td colspan="2" class="text-end">{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        <tr class="table-active">
                            <td colspan="5" class="text-end fw-bold h5">Total:</td>
                            <td colspan="2" class="text-end fw-bold h5">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold text-success">Paid:</td>
                            <td colspan="2" class="text-end fw-semibold text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold {{ $invoice->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">Balance:</td>
                            <td colspan="2" class="text-end fw-bold {{ $invoice->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->remaining_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($invoice->notes)
            <div class="mt-3">
                <h6 class="text-muted text-uppercase small fw-bold">Notes</h6>
                <p class="text-muted">{{ $invoice->notes }}</p>
            </div>
            @endif
        </x-card>
    </div>

    <div class="col-lg-4">
        <x-card title="Payment History" subtitle="Recorded payments">
            @if($invoice->payments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($invoice->payments as $payment)
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">{{ $payment->payment_number }}</div>
                            <div class="text-muted smaller">{{ $payment->payment_date->format('M d, Y') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">{{ number_format($payment->amount, 2) }}</div>
                            <div class="badge bg-info bg-opacity-10 text-info small">{{ $payment->payment_method }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-3">No payments recorded</p>
            @endif
        </x-card>

        <x-card title="Invoice Info" subtitle="Metadata">
            <ul class="list-unstyled mb-0">
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Created By</span>
                    <span class="fw-semibold">{{ $invoice->creator->name ?? 'N/A' }}</span>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Created At</span>
                    <span class="fw-semibold">{{ $invoice->created_at->format('M d, Y H:i') }}</span>
                </li>
                <li class="d-flex justify-content-between py-2">
                    <span class="text-muted">UUID</span>
                    <span class="fw-semibold small text-muted">{{ $invoice->uuid }}</span>
                </li>
            </ul>
        </x-card>
    </div>
</div>
@endsection
