<?php

namespace App\Repositories\Interfaces;

interface BudgetRepositoryInterface extends BaseRepositoryInterface
{
    public function getByType(string $type);
    public function getByYear(int $year);
    public function getByCategory(string $category);
    public function getActiveBudgets();
    public function getOverBudget();
}
