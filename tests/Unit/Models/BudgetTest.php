<?php

use App\Models\Budget;

describe('Budget Model', function () {
    it('calculates usage percentage correctly', function () {
        $budget = Budget::factory()->create([
            'allocated_amount' => 10000,
            'spent_amount' => 7500,
            'remaining_amount' => 2500,
        ]);

        expect($budget->usage_percent)->toBe(75.0);
    });

    it('returns zero usage when allocated is zero', function () {
        $budget = Budget::factory()->create([
            'allocated_amount' => 0,
            'spent_amount' => 0,
        ]);

        expect($budget->usage_percent)->toBe(0.0);
    });

    it('detects over budget status', function () {
        $budget = Budget::factory()->overBudget()->create();

        expect($budget->isOverBudget())->toBeTrue();
    });

    it('does not flag under budget as over', function () {
        $budget = Budget::factory()->create([
            'allocated_amount' => 10000,
            'spent_amount' => 5000,
        ]);

        expect($budget->isOverBudget())->toBeFalse();
    });

    it('caps usage percentage at 100', function () {
        $budget = Budget::factory()->overBudget()->create();

        expect($budget->usage_percent)->toBe(100.0);
    });

    it('recalculates spent from expenses', function () {
        $budget = Budget::factory()->create([
            'allocated_amount' => 50000,
            'spent_amount' => 0,
            'remaining_amount' => 50000,
        ]);

        \App\Models\Expense::factory()->count(3)->create([
            'budget_id' => $budget->id,
            'total_amount' => 1000,
            'status' => 1,
        ]);

        $budget->recalculateSpent();

        expect($budget->spent_amount)->toBe(3000.0);
        expect($budget->remaining_amount)->toBe(47000.0);
    });
});
