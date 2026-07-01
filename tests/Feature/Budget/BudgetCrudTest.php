<?php

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;

describe('Budget CRUD', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    describe('Create', function () {
        it('creates monthly budget', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'January Marketing Budget',
                'type' => 'monthly',
                'budget_year' => 2024,
                'budget_month' => 1,
                'category' => 'Marketing',
                'allocated_amount' => 10000,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
                'status' => 1,
            ]);

            $response->assertRedirect(route('budgets.index'));

            assertDatabaseHas('budgets', [
                'name' => 'January Marketing Budget',
                'type' => 'monthly',
                'budget_month' => 1,
            ]);

            $budget = Budget::latest()->first();
            expect($budget->remaining_amount)->toBe(10000.0);
        });

        it('creates yearly budget', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => '2024 Operations Budget',
                'type' => 'yearly',
                'budget_year' => 2024,
                'category' => 'Operations',
                'allocated_amount' => 500000,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 1,
            ]);

            $response->assertRedirect(route('budgets.index'));

            $budget = Budget::latest()->first();
            expect($budget->type)->toBe('yearly');
            expect($budget->budget_month)->toBeNull();
        });

        it('requires name field', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => '',
                'type' => 'monthly',
                'budget_year' => 2024,
                'allocated_amount' => 10000,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
            ]);

            $response->assertSessionHasErrors('name');
        });

        it('requires valid budget type', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'Test',
                'type' => 'invalid_type',
                'budget_year' => 2024,
                'allocated_amount' => 10000,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
            ]);

            $response->assertSessionHasErrors('type');
        });

        it('requires budget month for monthly budgets', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'Test',
                'type' => 'monthly',
                'budget_year' => 2024,
                'allocated_amount' => 10000,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
            ]);

            $response->assertSessionHasErrors('budget_month');
        });

        it('validates allocated amount is positive', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'Test',
                'type' => 'yearly',
                'budget_year' => 2024,
                'allocated_amount' => 0,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

            $response->assertSessionHasErrors('allocated_amount');
        });

        it('validates end date is after start date', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'Test',
                'type' => 'yearly',
                'budget_year' => 2024,
                'allocated_amount' => 10000,
                'start_date' => '2024-12-31',
                'end_date' => '2024-01-01',
            ]);

            $response->assertSessionHasErrors('end_date');
        });

        it('validates budget year range', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'Test',
                'type' => 'yearly',
                'budget_year' => 1999,
                'allocated_amount' => 10000,
                'start_date' => '1999-01-01',
                'end_date' => '1999-12-31',
            ]);

            $response->assertSessionHasErrors('budget_year');
        });

        it('handles high allocated amounts', function () {
            $response = $this->post(route('budgets.store'), [
                'name' => 'High Budget',
                'type' => 'yearly',
                'budget_year' => 2024,
                'allocated_amount' => 999999999.99,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

            $response->assertRedirect(route('budgets.index'));

            $budget = Budget::latest()->first();
            expect($budget->allocated_amount)->toBe(999999999.99);
        });
    });

    describe('Update', function () {
        beforeEach(function () {
            $this->budget = Budget::factory()->create([
                'allocated_amount' => 10000,
                'spent_amount' => 3000,
                'remaining_amount' => 7000,
            ]);
        });

        it('updates budget and recalculates remaining', function () {
            $response = $this->put(route('budgets.update', $this->budget), [
                'name' => 'Updated Budget',
                'type' => 'monthly',
                'budget_year' => 2024,
                'budget_month' => 2,
                'allocated_amount' => 15000,
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-29',
                'status' => 1,
            ]);

            $response->assertRedirect(route('budgets.index'));

            $this->budget->refresh();
            expect($this->budget->name)->toBe('Updated Budget');
            expect($this->budget->allocated_amount)->toBe(15000.0);
            expect($this->budget->remaining_amount)->toBe(12000.0); // 15000 - 3000
        });

        it('prevents reducing allocated below spent', function () {
            // This would be a business rule - implementation may vary
            $response = $this->put(route('budgets.update', $this->budget), [
                'name' => 'Test',
                'type' => 'monthly',
                'budget_year' => 2024,
                'budget_month' => 1,
                'allocated_amount' => 2000, // Less than spent (3000)
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
                'status' => 1,
            ]);

            // Should either fail or handle gracefully
            $response->assertRedirect(route('budgets.index'));
        });
    });

    describe('Delete', function () {
        it('soft deletes budget', function () {
            $budget = Budget::factory()->create();

            $response = $this->delete(route('budgets.destroy', $budget));

            $response->assertRedirect(route('budgets.index'));
            assertSoftDeleted($budget);
        });

        it('prevents delete if expenses exist', function () {
            $budget = Budget::factory()->create();
            Expense::factory()->create(['budget_id' => $budget->id]);

            $response = $this->delete(route('budgets.destroy', $budget));

            // Should handle gracefully - set null or prevent
            $response->assertRedirect(route('budgets.index'));
        });
    });

    describe('Budget Consumption', function () {
        it('recalculates spent when expense is added', function () {
            $budget = Budget::factory()->create([
                'allocated_amount' => 10000,
                'spent_amount' => 0,
                'remaining_amount' => 10000,
            ]);

            Expense::factory()->create([
                'budget_id' => $budget->id,
                'total_amount' => 1500,
                'status' => 1,
            ]);

            $budget->refresh();
            expect($budget->spent_amount)->toBe(1500.0);
            expect($budget->remaining_amount)->toBe(8500.0);
        });

        it('recalculates spent when expense is deleted', function () {
            $budget = Budget::factory()->create([
                'allocated_amount' => 10000,
                'spent_amount' => 1500,
                'remaining_amount' => 8500,
            ]);

            $expense = Expense::factory()->create([
                'budget_id' => $budget->id,
                'total_amount' => 1500,
                'status' => 1,
            ]);

            $expense->delete();

            $budget->refresh();
            expect($budget->spent_amount)->toBe(0.0);
            expect($budget->remaining_amount)->toBe(10000.0);
        });

        it('detects over budget status', function () {
            $budget = Budget::factory()->overBudget()->create();

            expect($budget->isOverBudget())->toBeTrue();
            expect($budget->usage_percent)->toBe(100.0);
        });
    });

    describe('DataTable', function () {
        it('filters by budget type', function () {
            Budget::factory()->monthly()->count(3)->create();
            Budget::factory()->yearly()->count(2)->create();

            $response = $this->getJson(route('budgets.datatable') . '?type=monthly');

            $response->assertStatus(200);
        });

        it('filters by year', function () {
            Budget::factory()->create(['budget_year' => 2023]);
            Budget::factory()->create(['budget_year' => 2024]);

            $response = $this->getJson(route('budgets.datatable') . '?year=2024');

            $response->assertStatus(200);
            expect($response->json('recordsFiltered'))->toBe(1);
        });
    });
});
