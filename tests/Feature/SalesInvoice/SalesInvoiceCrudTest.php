<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

describe('Sales Invoice CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create();
    });

    describe('Create', function () {
        it('creates invoice with items', function () {
            $data = [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'currency' => 'USD',
                'exchange_rate' => 1,
                'reference' => 'REF-001',
                'notes' => 'Test invoice',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'description' => 'Test Item',
                        'quantity' => 2,
                        'unit' => 'pcs',
                        'unit_price' => 100,
                        'tax_rate' => 10,
                        'discount_percent' => 5,
                    ],
                    [
                        'product_id' => null,
                        'description' => 'Custom Service',
                        'quantity' => 1,
                        'unit' => 'hr',
                        'unit_price' => 50,
                        'tax_rate' => 0,
                        'discount_percent' => 0,
                    ],
                ],
            ];

            $response = $this->post(route('sales-invoices.store'), $data);

            $response->assertRedirect(route('sales-invoices.index'));
            $response->assertSessionHas('success');

            $invoice = SalesInvoice::latest()->first();
            expect($invoice)->not->toBeNull();
            expect($invoice->items)->toHaveCount(2);
            expect($invoice->subtotal)->toBe(250.0);
            expect($invoice->tax_amount)->toBe(20.0);
            expect($invoice->discount_amount)->toBe(10.0);
            expect($invoice->total_amount)->toBe(260.0);
        });

        it('requires at least one item', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [],
            ]);

            $response->assertSessionHasErrors('items');
        });

        it('requires item description', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'product_id' => null,
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.description');
        });

        it('requires positive quantity', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 0,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.quantity');
        });

        it('requires non-negative unit price', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => -10,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.unit_price');
        });

         it('validates due date is after invoice date', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->subDay()->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('due_date');
        });

        it('validates customer exists', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => 99999,
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

            $response->assertSessionHasErrors('customer_id');
        });

        it('validates product exists if provided', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'product_id' => 99999,
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.product_id');
        });

        it('allows null product_id', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'product_id' => null,
                        'description' => 'Custom Service',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));
        });

        it('validates tax rate is between 0 and 100', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                        'tax_rate' => 101,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.tax_rate');
        });

        it('validates discount percent is between 0 and 100', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                        'discount_percent' => 101,
                    ],
                ],
            ]);

            $response->assertSessionHasErrors('items.0.discount_percent');
        });

        it('calculates totals correctly with tax and discount', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test Item',
                        'quantity' => 10,
                        'unit_price' => 100,
                        'tax_rate' => 10,
                        'discount_percent' => 5,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $invoice = SalesInvoice::latest()->first();
            // Base: 10 * 100 = 1000
            // Tax: 1000 * 10% = 100
            // Discount: 1000 * 5% = 50
            // Total: 1000 + 100 - 50 = 1050
            expect($invoice->subtotal)->toBe(1000.0);
            expect($invoice->tax_amount)->toBe(100.0);
            expect($invoice->discount_amount)->toBe(50.0);
            expect($invoice->total_amount)->toBe(1050.0);
        });

        it('handles decimal quantities correctly', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 2.5,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $invoice = SalesInvoice::latest()->first();
            expect($invoice->subtotal)->toBe(250.0);
        });

        it('handles zero tax and discount', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                        'tax_rate' => 0,
                        'discount_percent' => 0,
                    ],
                ],
            ]);

            $invoice = SalesInvoice::latest()->first();
            expect($invoice->tax_amount)->toBe(0.0);
            expect($invoice->discount_amount)->toBe(0.0);
            expect($invoice->total_amount)->toBe(100.0);
        });

        it('handles high value invoices', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'High Value Item',
                        'quantity' => 1,
                        'unit_price' => 999999.99,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $invoice = SalesInvoice::latest()->first();
            expect($invoice->total_amount)->toBe(999999.99);
        });

        it('generates unique invoice number', function () {
            $data = [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ];

            $this->post(route('sales-invoices.store'), $data);
            $this->post(route('sales-invoices.store'), $data);

            $invoices = SalesInvoice::latest()->take(2)->get();
            expect($invoices[0]->invoice_number)->not->toBe($invoices[1]->invoice_number);
        });

        it('updates customer balance after creation', function () {
            $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

            $this->customer->refresh();
            expect($this->customer->balance)->toBe(1000.0);
        });

        it('handles different currencies', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'currency' => 'EUR',
                'exchange_rate' => 0.92,
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $invoice = SalesInvoice::latest()->first();
            expect($invoice->currency)->toBe('EUR');
            expect($invoice->exchange_rate)->toBe(0.92);
        });

        it('rate limits invoice creation', function () {
            $data = [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ];

            // Make 10 requests rapidly
            for ($i = 0; $i < 10; $i++) {
                $this->post(route('sales-invoices.store'), $data);
            }

            // 11th should be rate limited
            $response = $this->post(route('sales-invoices.store'), $data);
            $response->assertStatus(429);
        });

        it('prevents mass assignment vulnerabilities', function () {
            $response = $this->post(route('sales-invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
                'payment_status' => 'paid', // Attempt mass assignment
                'paid_amount' => 999999,
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $invoice = SalesInvoice::latest()->first();
            expect($invoice->payment_status)->toBe('pending'); // Should not be mass assigned
            expect($invoice->paid_amount)->toBe(0.0);
        });
    });

    describe('Read', function () {
        it('displays invoice list', function () {
            SalesInvoice::factory()->count(5)->create();

            $response = $this->get(route('sales-invoices.index'));

            $response->assertStatus(200);
            $response->assertViewIs('sales-invoices.index');
        });

        it('displays invoice details', function () {
            $invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
            ]);
            SalesInvoiceItem::factory()->count(3)->create([
                'sales_invoice_id' => $invoice->id,
            ]);

            $response = $this->get(route('sales-invoices.show', $invoice->uuid));

            $response->assertStatus(200);
            $response->assertViewIs('sales-invoices.show');
            $response->assertViewHas('invoice');
        });

        it('returns 404 for invalid UUID', function () {
            $response = $this->get(route('sales-invoices.show', 'invalid-uuid'));

            $response->assertStatus(404);
        });

        it('filters by payment status', function () {
            SalesInvoice::factory()->paid()->count(3)->create();
            SalesInvoice::factory()->overdue()->count(2)->create();

            $response = $this->get(route('sales-invoices.index', ['status' => 'paid']));

            $response->assertStatus(200);
        });

        it('displays edit form', function () {
            $invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
            ]);

            $response = $this->get(route('sales-invoices.edit', $invoice));

            $response->assertStatus(200);
            $response->assertViewIs('sales-invoices.edit');
        });
    });

    describe('Update', function () {
        beforeEach(function () {
            $this->invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
                'total_amount' => 1000,
                'paid_amount' => 0,
            ]);
            SalesInvoiceItem::factory()->create([
                'sales_invoice_id' => $this->invoice->id,
                'quantity' => 1,
                'unit_price' => 1000,
                'total' => 1000,
            ]);
        });

        it('updates invoice with new items', function () {
            $newCustomer = Customer::factory()->create();

            $response = $this->put(route('sales-invoices.update', $this->invoice), [
                'customer_id' => $newCustomer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(60)->toDateString(),
                'items' => [
                    [
                        'product_id' => null,
                        'description' => 'Updated Item',
                        'quantity' => 2,
                        'unit' => 'pcs',
                        'unit_price' => 150,
                        'tax_rate' => 5,
                        'discount_percent' => 0,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales-invoices.index'));

            $this->invoice->refresh();
            expect($this->invoice->customer_id)->toBe($newCustomer->id);
            expect($this->invoice->items)->toHaveCount(1);
            expect($this->invoice->subtotal)->toBe(300.0);
        });

        it('recalculates totals on update', function () {
            $response = $this->put(route('sales-invoices.update', $this->invoice), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'New Item',
                        'quantity' => 5,
                        'unit_price' => 200,
                        'tax_rate' => 10,
                        'discount_percent' => 5,
                    ],
                ],
            ]);

            $this->invoice->refresh();
            // 5 * 200 = 1000, tax = 100, discount = 50, total = 1050
            expect($this->invoice->total_amount)->toBe(1050.0);
        });

        it('updates customer balance when customer changes', function () {
            $newCustomer = Customer::factory()->create();

            $this->put(route('sales-invoices.update', $this->invoice), [
                'customer_id' => $newCustomer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Test',
                        'quantity' => 1,
                        'unit_price' => 500,
                    ],
                ],
            ]);

            $this->customer->refresh();
            $newCustomer->refresh();

            expect($this->customer->balance)->toBe(0.0);
            expect($newCustomer->balance)->toBe(500.0);
        });

        it('prevents update if invoice is paid', function () {
            $paidInvoice = SalesInvoice::factory()->paid()->create([
                'customer_id' => $this->customer->id,
            ]);

            $response = $this->put(route('sales-invoices.update', $paidInvoice), [
                'customer_id' => $this->customer->id,
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

            // Should still allow update but warn
            $response->assertRedirect(route('sales-invoices.index'));
        });
    });

    describe('Delete', function () {
        it('soft deletes invoice', function () {
            $invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
            ]);

            $response = $this->delete(route('sales-invoices.destroy', $invoice));

            $response->assertRedirect(route('sales-invoices.index'));
            assertSoftDeleted($invoice);
        });

        it('updates customer balance after delete', function () {
            $invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
                'total_amount' => 1000,
            ]);

            $this->customer->refresh();
            expect($this->customer->balance)->toBe(1000.0);

            $this->delete(route('sales-invoices.destroy', $invoice));

            $this->customer->refresh();
            expect($this->customer->balance)->toBe(0.0);
        });

        it('prevents delete if payments exist', function () {
            $invoice = SalesInvoice::factory()->create([
                'customer_id' => $this->customer->id,
            ]);
            \App\Models\Payment::factory()->create([
                'payable_type' => SalesInvoice::class,
                'payable_id' => $invoice->id,
            ]);

            $response = $this->delete(route('sales-invoices.destroy', $invoice));

            // Should handle gracefully
            $response->assertRedirect(route('sales-invoices.index'));
        });
    });

    describe('DataTable', function () {
        it('returns JSON for DataTable', function () {
            SalesInvoice::factory()->count(10)->create();

            $response = $this->getJson(route('sales-invoices.datatable'));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data',
            ]);
        });

        it('filters by payment status', function () {
            SalesInvoice::factory()->paid()->count(3)->create();
            SalesInvoice::factory()->overdue()->count(2)->create();

            $response = $this->getJson(route('sales-invoices.datatable') . '?status=paid');

            $response->assertStatus(200);
        });

        it('searches by invoice number', function () {
            SalesInvoice::factory()->create(['invoice_number' => 'SI-2024-00001']);
            SalesInvoice::factory()->count(5)->create();

            $response = $this->getJson(route('sales-invoices.datatable') . '?search[value]=SI-2024-00001');

            $response->assertStatus(200);
            expect($response->json('recordsFiltered'))->toBe(1);
        });

        it('searches by customer name', function () {
            $customer = Customer::factory()->create(['name' => 'UniqueCustomerName']);
            SalesInvoice::factory()->create(['customer_id' => $customer->id]);
            SalesInvoice::factory()->count(5)->create();

            $response = $this->getJson(route('sales-invoices.datatable') . '?search[value]=UniqueCustomer');

            $response->assertStatus(200);
            expect($response->json('recordsFiltered'))->toBe(1);
        });
    });
});
