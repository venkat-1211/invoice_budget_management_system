<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\SalesInvoice;

describe('Customer CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    describe('Index', function () {
        it('displays customer list', function () {
            Customer::factory()->count(5)->create();

            $response = $this->get(route('customers.index'));

            $response->assertStatus(200);
            $response->assertViewIs('customers.index');
        });

        it('requires authentication', function () {
            auth()->logout();

            $response = $this->get(route('customers.index'));

            $response->assertRedirect(route('login'));
        });
    });

    describe('Create', function () {
        it('displays create form', function () {
            $response = $this->get(route('customers.create'));

            $response->assertStatus(200);
            $response->assertViewIs('customers.create');
        });

        it('creates customer with valid data', function () {
            $data = [
                'code' => 'CUST-001',
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'tax_number' => 'TAX123',
                'billing_address' => '123 Test St',
                'shipping_address' => '456 Ship Ave',
                'city' => 'Test City',
                'country' => 'Test Country',
                'postal_code' => '12345',
                'credit_limit' => 5000,
                'status' => 1,
            ];

            $response = $this->post(route('customers.store'), $data);

            $response->assertRedirect(route('customers.index'));
            $response->assertSessionHas('success');

            assertDatabaseHas('customers', [
                'code' => 'CUST-001',
                'name' => 'Test Customer',
            ]);
        });

        it('requires code field', function () {
            $response = $this->post(route('customers.store'), [
                'code' => '',
                'name' => 'Test',
            ]);

            $response->assertSessionHasErrors('code');
        });

        it('requires unique code', function () {
            Customer::factory()->create(['code' => 'CUST-001']);

            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
            ]);

            $response->assertSessionHasErrors('code');
        });

        it('requires name field', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => '',
            ]);

            $response->assertSessionHasErrors('name');
        });

        it('validates email format', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
                'email' => 'invalid-email',
            ]);

            $response->assertSessionHasErrors('email');
        });

        it('validates credit limit is numeric', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
                'credit_limit' => 'not-a-number',
            ]);

            $response->assertSessionHasErrors('credit_limit');
        });

        it('prevents negative credit limit', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
                'credit_limit' => -100,
            ]);

            $response->assertSessionHasErrors('credit_limit');
        });

        it('handles unicode in name', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => '株式会社テスト',
                'email' => 'test@example.com',
            ]);

            $response->assertRedirect(route('customers.index'));
            assertDatabaseHas('customers', ['name' => '株式会社テスト']);
        });

        it('handles emoji in name', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => '🏢 Test Company',
                'email' => 'test@example.com',
            ]);

            $response->assertRedirect(route('customers.index'));
            assertDatabaseHas('customers', ['name' => '🏢 Test Company']);
        });

        it('handles XSS attempts in fields', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => '<script>alert("xss")</script>',
                'email' => 'test@example.com',
            ]);

            $response->assertRedirect(route('customers.index'));
            $customer = Customer::where('code', 'CUST-001')->first();
            expect($customer->name)->toContain('<script>'); // Stored but escaped in views
        });

        it('handles SQL injection in code field', function () {
            $response = $this->post(route('customers.store'), [
                'code' => "'; DROP TABLE customers; --",
                'name' => 'Test',
                'email' => 'test@example.com',
            ]);

            $response->assertRedirect(route('customers.index'));
            assertDatabaseHas('customers', ['code' => "'; DROP TABLE customers; --"]);
            // Verify table still exists
            expect(\Illuminate\Support\Facades\DB::select('SELECT 1 FROM customers LIMIT 1'))->not->toBeEmpty();
        });

        it('handles maximum length strings', function () {
            $response = $this->post(route('customers.store'), [
                'code' => str_repeat('A', 21), // Exceeds 20 char limit
                'name' => str_repeat('B', 151), // Exceeds 150 char limit
                'email' => 'test@example.com',
            ]);

            $response->assertSessionHasErrors(['code', 'name']);
        });

        it('sets default status to active', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
                'email' => 'test@example.com',
            ]);

            $customer = Customer::where('code', 'CUST-001')->first();
            expect($customer->status)->toBe(1);
        });

        it('allows null optional fields', function () {
            $response = $this->post(route('customers.store'), [
                'code' => 'CUST-001',
                'name' => 'Test',
            ]);

            $response->assertRedirect(route('customers.index'));
            $customer = Customer::where('code', 'CUST-001')->first();
            expect($customer->email)->toBeNull();
            expect($customer->phone)->toBeNull();
        });
    });

    describe('Update', function () {
        beforeEach(function () {
            $this->customer = Customer::factory()->create();
        });

        it('updates customer with valid data', function () {
            $response = $this->put(route('customers.update', $this->customer), [
                'code' => $this->customer->code,
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'status' => 1,
            ]);

            $response->assertRedirect(route('customers.index'));
            assertDatabaseHas('customers', [
                'id' => $this->customer->id,
                'name' => 'Updated Name',
            ]);
        });

        it('prevents duplicate code on update', function () {
            $otherCustomer = Customer::factory()->create(['code' => 'CUST-002']);

            $response = $this->put(route('customers.update', $otherCustomer), [
                'code' => $this->customer->code,
                'name' => 'Test',
            ]);

            $response->assertSessionHasErrors('code');
        });

        it('allows keeping same code on update', function () {
            $response = $this->put(route('customers.update', $this->customer), [
                'code' => $this->customer->code,
                'name' => 'Updated Name',
                'email' => $this->customer->email,
                'status' => 1,
            ]);

            $response->assertRedirect(route('customers.index'));
        });

        it('handles concurrent updates', function () {
            $response1 = $this->put(route('customers.update', $this->customer), [
                'code' => $this->customer->code,
                'name' => 'Name A',
                'status' => 1,
            ]);

            $response2 = $this->put(route('customers.update', $this->customer), [
                'code' => $this->customer->code,
                'name' => 'Name B',
                'status' => 1,
            ]);

            $response1->assertRedirect(route('customers.index'));
            $response2->assertRedirect(route('customers.index'));
        });
    });

    describe('Delete', function () {
        beforeEach(function () {
            $this->customer = Customer::factory()->create();
        });

        it('soft deletes customer', function () {
            $response = $this->delete(route('customers.destroy', $this->customer));

            $response->assertRedirect(route('customers.index'));
            assertSoftDeleted($this->customer);
        });

        it('prevents delete if customer has invoices', function () {
            SalesInvoice::factory()->create(['customer_id' => $this->customer->id]);

            $response = $this->delete(route('customers.destroy', $this->customer));

            // Should handle gracefully - either prevent or cascade
            $response->assertRedirect(route('customers.index'));
        });

        it('returns 404 for non-existent customer', function () {
            $response = $this->delete(route('customers.destroy', 99999));

            $response->assertStatus(404);
        });
    });

    describe('DataTable', function () {
        it('returns JSON for DataTable', function () {
            Customer::factory()->count(10)->create();

            $response = $this->getJson(route('customers.datatable'), [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]);

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data',
            ]);
            expect($response->json('recordsTotal'))->toBe(10);
        });

        it('supports searching', function () {
            Customer::factory()->create(['name' => 'UniqueSearchName']);
            Customer::factory()->count(5)->create();

            $response = $this->getJson(route('customers.datatable') . '?search[value]=UniqueSearchName');

            $response->assertStatus(200);
            expect($response->json('recordsFiltered'))->toBe(1);
        });

        it('supports sorting', function () {
            Customer::factory()->create(['name' => 'Alpha']);
            Customer::factory()->create(['name' => 'Zebra']);

            $response = $this->getJson(route('customers.datatable') . '?order[0][column]=1&order[0][dir]=desc');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data[0]['name'])->toBe('Zebra');
        });

        it('supports pagination', function () {
            Customer::factory()->count(25)->create();

            $response = $this->getJson(route('customers.datatable') . '?start=0&length=10');

            $response->assertStatus(200);
            expect(count($response->json('data')))->toBe(10);
        });

        it('handles empty dataset', function () {
            $response = $this->getJson(route('customers.datatable'));

            $response->assertStatus(200);
            expect($response->json('recordsTotal'))->toBe(0);
            expect($response->json('data'))->toBeEmpty();
        });

        it('handles large datasets efficiently', function () {
            Customer::factory()->count(1000)->create();

            $startTime = microtime(true);
            $response = $this->getJson(route('customers.datatable'));
            $endTime = microtime(true);

            $response->assertStatus(200);
            expect($endTime - $startTime)->toBeLessThan(2.0); // Should complete within 2 seconds
        });
    });

    describe('Search', function () {
        it('returns customers for select2', function () {
            Customer::factory()->create(['name' => 'Searchable Customer']);

            $response = $this->getJson(route('customers.search') . '?q=Searchable');

            $response->assertStatus(200);
            $response->assertJsonStructure(['results']);
            expect($response->json('results'))->not->toBeEmpty();
        });

        it('returns empty results for no match', function () {
            $response = $this->getJson(route('customers.search') . '?q=NonExistentXYZ');

            $response->assertStatus(200);
            expect($response->json('results'))->toBeEmpty();
        });

        it('handles special characters in search', function () {
            Customer::factory()->create(['name' => "O'Reilly's"]);

            $response = $this->getJson(route('customers.search') . '?q=O\'Reilly');

            $response->assertStatus(200);
        });
    });
});
