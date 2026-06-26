<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function findByExpenseNumber(string $number)
    {
        return DB::table('expenses')
            ->where('expense_number', $number)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getByBudget(int $budgetId)
    {
        return DB::table('expenses')
            ->where('budget_id', $budgetId)
            ->whereNull('deleted_at')
            ->orderByDesc('expense_date')
            ->get();
    }

    public function getByCategory(string $category)
    {
        return DB::table('expenses')
            ->where('category', $category)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate)
    {
        return DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->get();
    }

    public function getRecentExpenses(int $limit = 10)
    {
        return DB::table('expenses')
            ->select('expenses.*', 'budgets.name as budget_name')
            ->leftJoin('budgets', 'expenses.budget_id', '=', 'budgets.id')
            ->whereNull('expenses.deleted_at')
            ->orderByDesc('expenses.created_at')
            ->limit($limit)
            ->get();
    }
}
