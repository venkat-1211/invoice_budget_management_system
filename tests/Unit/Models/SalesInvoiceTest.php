<?php

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;

describe('SalesInvoice Model', function () {
    beforeEach(function () {
        $this->invoice = SalesInvoice::factory()->create([
            'total_amount' => 1000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(30),
        ]);
    });

    it('calculates remaining amount correctly', function () {
        expect($this->invoice->remaining_amount)->toBe(1000.0);
    });

    it('detects overdue invoices', function () {
        $overdue = SalesInvoice::factory()->overdue()->create();

        expect($overdue->isOverdue())->toBeTrue();
    });

    it('does not flag future due date as overdue', function () {
        expect($this->invoice->isOverdue())->toBeFalse();
    });

    it('does not flag paid invoice as overdue', function () {
        $paid = SalesInvoice::factory()->paid()->create([
            'due_date' => now()->subDays(10),
        ]);

        expect($paid->isOverdue())->toBeFalse();
    });

    it('recalculates totals from items', function () {
        $item1 = SalesInvoiceItem::factory()->create([
            'sales_invoice_id' => $this->invoice->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax_rate' => 10,
            'discount_percent' => 5,
            'total' => 210, // (2*100) + 20 - 10
        ]);

        $item2 = SalesInvoiceItem::factory()->create([
            'sales_invoice_id' => $this->invoice->id,
            'quantity' => 1,
            'unit_price' => 50,
            'tax_rate' => 0,
            'discount_percent' => 0,
            'total' => 50,
        ]);

        $this->invoice->recalculateTotals();

        expect($this->invoice->subtotal)->toBe(250.0);
        expect($this->invoice->tax_amount)->toBe(20.0);
        expect($this->invoice->discount_amount)->toBe(10.0);
        expect($this->invoice->total_amount)->toBe(260.0);
    });

    it('updates payment status to paid when fully paid', function () {
        $this->invoice->paid_amount = $this->invoice->total_amount;
        $this->invoice->updatePaymentStatus();

        expect($this->invoice->payment_status)->toBe('paid');
    });

    it('updates payment status to partial when partially paid', function () {
        $this->invoice->paid_amount = $this->invoice->total_amount / 2;
        $this->invoice->updatePaymentStatus();

        expect($this->invoice->payment_status)->toBe('partial');
    });

    it('updates payment status to overdue when past due and unpaid', function () {
        $this->invoice->due_date = now()->subDay();
        $this->invoice->updatePaymentStatus();

        expect($this->invoice->payment_status)->toBe('overdue');
    });
});
