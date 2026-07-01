<?php

use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\User;

describe('Purchase Invoice CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->supplier = Supplier::factory()->create();
        $this->product = Product::factory()->create();
    });

    describe('Create', function () {
        it('creates purchase invoice with items', function () {
            $response = $this->post(route('purchase-invoices.store'), [
                'supplier_id' => $this->supplier->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'currency' => 'USD',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'description' => 'Purchase Item',
                        'quantity' => 10,
                        'unit' => 'pcs',
                        'unit_price' => 50,
                        'tax_rate' => 10,
                        'discount_percent' => 0,
                    ],
                ],
            ]);

            $response->assertRedirect(route('purchase-invoices.index'));

            $invoice = PurchaseInvoice::latest()->first();
            expect($invoice)->not->toBeNull();
            expect($invoice->supplier_id)->toBe($this->supplier->id);
            expect($invoice->items)->toHaveCount(1);
            expect($invoice->subtotal)->toBe(500.0);
            expect($invoice->tax_amount)->toBe(50.0);
            expect($invoice->total_amount)->toBe(550.0);
        });

        it('updates supplier balance after creation', function () {
            $this->post(route('purchase-invoices.store'), [
                'supplier_id' => $this->supplier->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 2000,
                    ],
                ],
            ]);

            $this->supplier->refresh();
            expect($this->supplier->balance)->toBe(2000.0);
        });

        it('validates supplier exists', function () {
            $response = $this->post(route('purchase-invoices.store'), [
                'supplier_id' => 99999,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('supplier_id');
        });

        it('handles same validation rules as sales invoice', function () {
            $response = $this->post(route('purchase-invoices.store'), [
                'supplier_id' => $this->supplier->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->subDay()->toDateString(),
                'items' => [],
            ]);

            $response->assertSessionHasErrors(['due_date', 'items']);
        });
    });

    describe('Read', function () {
        it('displays purchase invoice details', function () {
            $invoice = PurchaseInvoice::factory()->create([
                'supplier_id' => $this->supplier->id,
            ]);

            $response = $this->get(route('purchase-invoices.show', $invoice->uuid));

            $response->assertStatus(200);
            $response->assertViewIs('purchase-invoices.show');
        });
    });

    describe('Update', function () {
        it('updates purchase invoice', function () {
            $invoice = PurchaseInvoice::factory()->create([
                'supplier_id' => $this->supplier->id,
            ]);

            $newSupplier = Supplier::factory()->create();

            $response = $this->put(route('purchase-invoices.update', $invoice), [
                'supplier_id' => $newSupplier->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(60)->toDateString(),
                'items' => [
                    [
                        'description' => 'Updated Item',
                        'quantity' => 5,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertRedirect(route('purchase-invoices.index'));

            $invoice->refresh();
            expect($invoice->supplier_id)->toBe($newSupplier->id);
        });
    });

    describe('Delete', function () {
        it('soft deletes purchase invoice', function () {
            $invoice = PurchaseInvoice::factory()->create([
                'supplier_id' => $this->supplier->id,
            ]);

            $response = $this->delete(route('purchase-invoices.destroy', $invoice));

            $response->assertRedirect(route('purchase-invoices.index'));
            assertSoftDeleted($invoice);
        });

        it('updates supplier balance after delete', function () {
            $invoice = PurchaseInvoice::factory()->create([
                'supplier_id' => $this->supplier->id,
                'total_amount' => 1000,
            ]);

            $this->delete(route('purchase-invoices.destroy', $invoice));

            $this->supplier->refresh();
            expect($this->supplier->balance)->toBe(0.0);
        });
    });
});
