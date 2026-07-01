<?php

use App\Models\Customer;
use App\Models\SalesInvoice;

describe('Customer Model', function () {
    beforeEach(function () {
        $this->customer = Customer::factory()->create();
    });

    it('has a valid UUID', function () {
        expect($this->customer->uuid)->toBeValidUuid();
    });

    it('can have multiple sales invoices', function () {
        SalesInvoice::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);

        expect($this->customer->salesInvoices)->toHaveCount(3);
    });

    it('calculates balance correctly from invoices', function () {
        $invoice1 = SalesInvoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 1000,
            'paid_amount' => 400,
            'payment_status' => 'partial',
        ]);

        $invoice2 = SalesInvoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 500,
            'paid_amount' => 500,
            'payment_status' => 'paid',
        ]);

        $this->customer->updateBalance();

        expect($this->customer->balance)->toBe(600.0);
    });

    it('returns zero balance when no invoices exist', function () {
        $this->customer->updateBalance();

        expect($this->customer->balance)->toBe(0.0);
    });

    it('respects credit limit', function () {
        $customer = Customer::factory()->create([
            'credit_limit' => 5000,
            'balance' => 0,
        ]);

        expect($customer->credit_limit)->toBe(5000.0);
    });

    it('can be soft deleted', function () {
        $this->customer->delete();

        expect($this->customer->fresh()->deleted_at)->not->toBeNull();
        assertSoftDeleted($this->customer);
    });

    it('can be restored after soft delete', function () {
        $this->customer->delete();
        $this->customer->restore();

        expect($this->customer->fresh()->deleted_at)->toBeNull();
    });

    it('stores unicode names correctly', function () {
        $customer = Customer::factory()->withUnicodeName()->create();

        expect($customer->fresh()->name)->toContain('株式会社');
    });

    it('stores emoji in names correctly', function () {
        $customer = Customer::factory()->withEmojiName()->create();

        expect($customer->fresh()->name)->toContain('🏢');
    });
});
