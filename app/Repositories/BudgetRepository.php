<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    public function __construct(Budget $model)
    {
        parent::__construct($model);
    }

    public function getByType(string $type)
    {
        return DB::table('budgets')
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getByYear(int $year)
    {
        return DB::table('budgets')
            ->where('budget_year', $year)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getByCategory(string $category)
    {
        return DB::table('budgets')
            ->where('category', $category)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getActiveBudgets()
    {
        return DB::table('budgets')
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereNull('deleted_at')
            ->get();
    }

    public function getOverBudget()
    {
        return DB::table('budgets')
            ->whereRaw('spent_amount > allocated_amount')
            ->whereNull('deleted_at')
            ->get();
    }
}
