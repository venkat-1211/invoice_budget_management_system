<?php

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\User;

describe('Expense CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->budget = Budget::factory()->create([
            'allocated_amount' => 10000,
            'spent_amount' => 0,
            'remaining_amount' => 10000,
        ]);
        $this->supplier = Supplier::factory()->create();
    });

    describe('Create', function () {
        it('creates expense linked to budget', function () {
            $response = $this->post(route('expenses.store'), [
                'budget_id' => $this->budget->id,
                'supplier_id' => $this->supplier->id,
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 500,
                'tax_amount' => 50,
                'currency' => 'USD',
                'description' => 'Office supplies',
                'status' => 1,
            ]);

            $response->assertRedirect(route('expenses.index'));

            $expense = Expense::latest()->first();
            expect($expense)->not->toBeNull();
            expect($expense->budget_id)->toBe($this->budget->id);
            expect($expense->total_amount)->toBe(550.0); // 500 + 50 tax

            $this->budget->refresh();
            expect($this->budget->spent_amount)->toBe(550.0);
            expect($this->budget->remaining_amount)->toBe(9450.0);
        });

        it('creates expense without budget', function () {
            $response = $this->post(route('expenses.store'), [
                'budget_id' => null,
                'expense_date' => now()->toDateString(),
                'category' => 'Travel',
                'payment_method' => 'credit_card',
                'amount' => 1000,
                'tax_amount' => 0,
                'currency' => 'USD',
                'status' => 1,
            ]);

            $response->assertRedirect(route('expenses.index'));

            $expense = Expense::latest()->first();
            expect($expense->budget_id)->toBeNull();
        });

        it('validates expense amount is positive', function () {
            $response = $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 0,
                'currency' => 'USD',
            ]);

            $response->assertSessionHasErrors('amount');
        });

        it('validates tax amount is non-negative', function () {
            $response = $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 100,
                'tax_amount' => -10,
                'currency' => 'USD',
            ]);

            $response->assertSessionHasErrors('tax_amount');
        });

        it('validates payment method', function () {
            $response = $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'invalid_method',
                'amount' => 100,
                'currency' => 'USD',
            ]);

            $response->assertSessionHasErrors('payment_method');
        });

        it('validates category is required', function () {
            $response = $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'amount' => 100,
                'currency' => 'USD',
            ]);

            $response->assertSessionHasErrors('category');
        });

        it('prevents expense exceeding budget', function () {
            $response = $this->post(route('expenses.store'), [
                'budget_id' => $this->budget->id,
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 999999,
                'currency' => 'USD',
                'status' => 1,
            ]);

            $response->assertSessionHasErrors('budget_id');
        });

        it('handles future expense dates', function () {
            $response = $this->post(route('expenses.store'), [
                'expense_date' => now()->addMonth()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 100,
                'currency' => 'USD',
                'status' => 1,
            ]);

            $response->assertRedirect(route('expenses.index'));
        });

        it('generates unique expense number', function () {
            $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 100,
                'currency' => 'USD',
            ]);

            $this->post(route('expenses.store'), [
                'expense_date' => now()->toDateString(),
                'category' => 'Travel',
                'payment_method' => 'cash',
                'amount' => 200,
                'currency' => 'USD',
            ]);

            $expenses = Expense::latest()->take(2)->get();
            expect($expenses[0]->expense_number)->not->toBe($expenses[1]->expense_number);
        });
    });

    describe('Update', function () {
        beforeEach(function () {
            $this->expense = Expense::factory()->create([
                'budget_id' => $this->budget->id,
                'amount' => 500,
                'tax_amount' => 50,
                'total_amount' => 550,
            ]);
        });

        it('updates expense and recalculates budget', function () {
            $newBudget = Budget::factory()->create([
                'allocated_amount' => 20000,
                'spent_amount' => 0,
                'remaining_amount' => 20000,
            ]);

            $response = $this->put(route('expenses.update', $this->expense), [
                'budget_id' => $newBudget->id,
                'expense_date' => now()->toDateString(),
                'category' => 'Updated Category',
                'payment_method' => 'bank_transfer',
                'amount' => 1000,
                'tax_amount' => 100,
                'currency' => 'EUR',
                'status' => 1,
            ]);

            $response->assertRedirect(route('expenses.index'));

            $this->expense->refresh();
            expect($this->expense->category)->toBe('Updated Category');
            expect($this->expense->total_amount)->toBe(1100.0);

            $this->budget->refresh();
            expect($this->budget->spent_amount)->toBe(0.0);

            $newBudget->refresh();
            expect($newBudget->spent_amount)->toBe(1100.0);
        });

        it('prevents update exceeding new budget', function () {
            $smallBudget = Budget::factory()->create([
                'allocated_amount' => 100,
                'spent_amount' => 0,
                'remaining_amount' => 100,
            ]);

            $response = $this->put(route('expenses.update', $this->expense), [
                'budget_id' => $smallBudget->id,
                'expense_date' => now()->toDateString(),
                'category' => 'Office',
                'payment_method' => 'cash',
                'amount' => 500,
                'currency' => 'USD',
                'status' => 1,
            ]);

            $response->assertSessionHasErrors('budget_id');
        });
    });

    describe('Delete', function () {
        it('soft deletes expense and updates budget', function () {
            $expense = Expense::factory()->create([
                'budget_id' => $this->budget->id,
                'total_amount' => 1000,
            ]);

            $response = $this->delete(route('expenses.destroy', $expense));

            $response->assertRedirect(route('expenses.index'));
            assertSoftDeleted($expense);

            $this->budget->refresh();
            expect($this->budget->spent_amount)->toBe(0.0);
            expect($this->budget->remaining_amount)->toBe(10000.0);
        });
    });

    describe('DataTable', function () {
        it('filters by category', function () {
            Expense::factory()->count(3)->create(['category' => 'Office']);
            Expense::factory()->count(2)->create(['category' => 'Travel']);

            $response = $this->getJson(route('expenses.datatable') . '?category=Office');

            $response->assertStatus(200);
            expect($response->json('recordsFiltered'))->toBe(3);
        });
    });
});
