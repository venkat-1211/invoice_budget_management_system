<?php

namespace App\Http\Controllers;

use App\DTOs\BudgetDTO;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository
    ) {}

    public function index(): View
    {
        return view('budgets.index');
    }

    public function create(): View
    {
        return view('budgets.create');
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $dto = new BudgetDTO(
                name: $request->name,
                type: $request->type,
                budget_year: $request->budget_year,
                budget_month: $request->budget_month,
                category: $request->category,
                allocated_amount: $request->allocated_amount,
                remaining_amount: $request->allocated_amount,
                description: $request->description,
                start_date: $request->start_date,
                end_date: $request->end_date,
                created_by: Auth::id(),
                status: $request->boolean('status', true) ? 1 : 0
            );

            $this->budgetRepository->create($dto->toArray());
        });

        return redirect()->route('budgets.index')
            ->with('success', 'Budget created successfully');
    }

    public function edit(int $id): View
    {
        $budget = $this->budgetRepository->find($id);
        abort_if(!$budget, 404);

        return view('budgets.edit', compact('budget'));
    }

    public function update(StoreBudgetRequest $request, int $id): RedirectResponse
    {
        $budget = \App\Models\Budget::findOrFail($id);

        DB::transaction(function () use ($request, $budget) {
            $oldAllocated = $budget->allocated_amount;
            $newAllocated = $request->allocated_amount;

            $dto = new BudgetDTO(
                name: $request->name,
                type: $request->type,
                budget_year: $request->budget_year,
                budget_month: $request->budget_month,
                category: $request->category,
                allocated_amount: $request->allocated_amount,
                spent_amount: $budget->spent_amount,
                remaining_amount: $newAllocated - $budget->spent_amount,
                description: $request->description,
                start_date: $request->start_date,
                end_date: $request->end_date,
                status: $request->boolean('status', true) ? 1 : 0
            );

            $this->budgetRepository->update($budget->id, $dto->toArray());
        });

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->budgetRepository->delete($id);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('budgets')
            ->select([
                'budgets.id',
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(budgets.uuid), 1, 8), '-',
                        SUBSTR(HEX(budgets.uuid), 9, 4), '-',
                        SUBSTR(HEX(budgets.uuid), 13, 4), '-',
                        SUBSTR(HEX(budgets.uuid), 17, 4), '-',
                        SUBSTR(HEX(budgets.uuid), 21)
                    )) as uuid
                "),
                'budgets.name',
                'budgets.type',
                'budgets.budget_year',
                'budgets.budget_month',
                'budgets.category',
                'budgets.allocated_amount',
                'budgets.spent_amount',
                'budgets.remaining_amount',
                'budgets.start_date',
                'budgets.end_date',
                'budgets.status',
                'budgets.created_at'
            ])
            ->whereNull('budgets.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('budgets.name', 'like', "%{$search}%")
                  ->orWhere('budgets.category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('budgets.type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('budgets.budget_year', $request->year);
        }

        $totalRecords = $query->count();

        $columns = ['name', 'type', 'budget_year', 'category', 'allocated_amount', 'spent_amount', 'status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('budgets.created_at');
        }

        $budgets = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $budgets
        ]);
    }
}
