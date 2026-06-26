<?php

namespace App\Http\Controllers;

use App\DTOs\ExpenseDTO;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Services\InvoiceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private BudgetRepositoryInterface $budgetRepository,
        private SupplierRepositoryInterface $supplierRepository,
        private InvoiceNumberService $invoiceNumberService
    ) {}

    public function index(): View
    {
        return view('expenses.index');
    }

    public function create(): View
    {
        $budgets = $this->budgetRepository->getActiveBudgets();
        $suppliers = $this->supplierRepository->getActiveSuppliers();

        return view('expenses.create', compact('budgets', 'suppliers'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $expenseNumber = $this->invoiceNumberService->generateExpenseNumber();
            $totalAmount = $request->amount + ($request->tax_amount ?? 0);

            $dto = new ExpenseDTO(
                expense_number: $expenseNumber,
                budget_id: $request->budget_id,
                supplier_id: $request->supplier_id,
                expense_date: $request->expense_date,
                category: $request->category,
                payment_method: $request->payment_method,
                reference: $request->reference,
                amount: $request->amount,
                tax_amount: $request->tax_amount ?? 0,
                total_amount: $totalAmount,
                currency: $request->currency,
                description: $request->description,
                created_by: Auth::id(),
                status: $request->boolean('status', true) ? 1 : 0
            );

            $this->expenseRepository->create($dto->toArray());
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully');
    }

    public function edit(int $id): View
    {
        $expense = $this->expenseRepository->find($id);
        abort_if(!$expense, 404);

        $budgets = $this->budgetRepository->getActiveBudgets();
        $suppliers = $this->supplierRepository->getActiveSuppliers();

        return view('expenses.edit', compact('expense', 'budgets', 'suppliers'));
    }

    public function update(StoreExpenseRequest $request, int $id): RedirectResponse
    {
        $expense = \App\Models\Expense::findOrFail($id);

        DB::transaction(function () use ($request, $expense) {
            $totalAmount = $request->amount + ($request->tax_amount ?? 0);

            $dto = new ExpenseDTO(
                budget_id: $request->budget_id,
                supplier_id: $request->supplier_id,
                expense_date: $request->expense_date,
                category: $request->category,
                payment_method: $request->payment_method,
                reference: $request->reference,
                amount: $request->amount,
                tax_amount: $request->tax_amount ?? 0,
                total_amount: $totalAmount,
                currency: $request->currency,
                description: $request->description,
                status: $request->boolean('status', true) ? 1 : 0
            );

            $this->expenseRepository->update($expense->id, $dto->toArray());
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->expenseRepository->delete($id);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('expenses')
            ->select([
                'expenses.id',
                'expenses.uuid',
                'expenses.expense_number',
                'expenses.expense_date',
                'expenses.category',
                'expenses.payment_method',
                'expenses.total_amount',
                'expenses.currency',
                'budgets.name as budget_name',
                'suppliers.name as supplier_name',
                'expenses.status',
                'expenses.created_at'
            ])
            ->leftJoin('budgets', 'expenses.budget_id', '=', 'budgets.id')
            ->leftJoin('suppliers', 'expenses.supplier_id', '=', 'suppliers.id')
            ->whereNull('expenses.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('expenses.expense_number', 'like', "%{$search}%")
                  ->orWhere('expenses.category', 'like', "%{$search}%")
                  ->orWhere('budgets.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('expenses.category', $request->category);
        }

        $totalRecords = $query->count();

        $columns = ['expense_number', 'expense_date', 'category', 'budget_name', 'total_amount', 'status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('expenses.created_at');
        }

        $expenses = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $expenses
        ]);
    }
}
