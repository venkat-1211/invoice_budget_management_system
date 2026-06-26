<?php

namespace App\Repositories\Interfaces;

interface ExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function findByExpenseNumber(string $number);
    public function getByBudget(int $budgetId);
    public function getByCategory(string $category);
    public function getByDateRange(string $startDate, string $endDate);
    public function getRecentExpenses(int $limit = 10);
}
