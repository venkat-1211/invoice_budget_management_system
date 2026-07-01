<?php

use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Payment;
use App\Models\User;

describe('Payment CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create();
        $this->salesInvoice = SalesInvoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'payment_status' => 'pending',
        ]);
    });

    describe('Create', function () {
        it('records payment for sales invoice', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 1000,
                'payment_method' => 'cash',
                'reference' => 'REF-001',
                'notes' => 'Full payment',
            ]);

            $response->assertRedirect(route('payments.index'));

            $payment = Payment::latest()->first();
            expect($payment)->not->toBeNull();
            expect($payment->amount)->toBe(1000.0);

            $this->salesInvoice->refresh();
            expect($this->salesInvoice->paid_amount)->toBe(1000.0);
            expect($this->salesInvoice->payment_status)->toBe('paid');

            $this->customer->refresh();
            expect($this->customer->balance)->toBe(0.0);
        });

        it('records partial payment', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 400,
                'payment_method' => 'bank_transfer',
            ]);

            $this->salesInvoice->refresh();
            expect($this->salesInvoice->paid_amount)->toBe(400.0);
            expect($this->salesInvoice->payment_status)->toBe('partial');
        });

        it('prevents payment exceeding invoice total', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 1500, // More than total (1000)
                'payment_method' => 'cash',
            ]);

            $response->assertSessionHasErrors('amount');
        });

        it('prevents payment for non-existent invoice', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => 99999,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'payment_method' => 'cash',
            ]);

            $response->assertSessionHasErrors('payable_id');
        });

        it('records payment for purchase invoice', function () {
            $supplier = \App\Models\Supplier::factory()->create();
            $purchaseInvoice = PurchaseInvoice::factory()->create([
                'supplier_id' => $supplier->id,
                'total_amount' => 2000,
                'paid_amount' => 0,
            ]);

            $response = $this->post(route('payments.store'), [
                'payable_type' => PurchaseInvoice::class,
                'payable_id' => $purchaseInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 2000,
                'payment_method' => 'check',
            ]);

            $response->assertRedirect(route('payments.index'));

            $purchaseInvoice->refresh();
            expect($purchaseInvoice->payment_status)->toBe('paid');
        });

        it('validates payment method', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'payment_method' => 'invalid_method',
            ]);

            $response->assertSessionHasErrors('payment_method');
        });

        it('validates positive amount', function () {
            $response = $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 0,
                'payment_method' => 'cash',
            ]);

            $response->assertSessionHasErrors('amount');
        });

        it('rate limits payment creation', function () {
            $data = [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 10,
                'payment_method' => 'cash',
            ];

            for ($i = 0; $i < 15; $i++) {
                $this->post(route('payments.store'), $data);
            }

            $response = $this->post(route('payments.store'), $data);
            $response->assertStatus(429);
        });

        it('generates unique payment number', function () {
            $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'payment_method' => 'cash',
            ]);

            $this->post(route('payments.store'), [
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'payment_method' => 'cash',
            ]);

            $payments = Payment::latest()->take(2)->get();
            expect($payments[0]->payment_number)->not->toBe($payments[1]->payment_number);
        });
    });

    describe('Delete', function () {
        it('deletes payment and reverses invoice status', function () {
            $payment = Payment::factory()->create([
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
                'amount' => 1000,
            ]);

            $this->salesInvoice->refresh();
            expect($this->salesInvoice->payment_status)->toBe('paid');

            $response = $this->delete(route('payments.destroy', $payment));

            $response->assertRedirect(route('payments.index'));

            $this->salesInvoice->refresh();
            expect($this->salesInvoice->paid_amount)->toBe(0.0);
            expect($this->salesInvoice->payment_status)->toBe('pending');
        });
    });

    describe('DataTable', function () {
        it('returns payment list', function () {
            Payment::factory()->count(5)->create();

            $response = $this->getJson(route('payments.datatable'));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data',
            ]);
        });

        it('includes invoice reference', function () {
            Payment::factory()->create([
                'payable_type' => SalesInvoice::class,
                'payable_id' => $this->salesInvoice->id,
            ]);

            $response = $this->getJson(route('payments.datatable'));

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data[0])->toHaveKey('invoice_ref');
        });
    });
});
