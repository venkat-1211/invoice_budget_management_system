<?php

namespace App\Http\Controllers;

use App\DTOs\SupplierDTO;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository
    ) {}

    public function index(): View
    {
        return view('suppliers.index');
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $dto = new SupplierDTO(...$request->validated());
        $this->supplierRepository->create($dto->toArray());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully');
    }

    public function edit(int $id): View
    {
        $supplier = $this->supplierRepository->find($id);
        abort_if(!$supplier, 404);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, int $id): RedirectResponse
    {
        $dto = new SupplierDTO(...$request->validated());
        $this->supplierRepository->update($id, $dto->toArray());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->supplierRepository->delete($id);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('suppliers')
            ->select([
                'suppliers.id',
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(suppliers.uuid), 1, 8), '-',
                        SUBSTR(HEX(suppliers.uuid), 9, 4), '-',
                        SUBSTR(HEX(suppliers.uuid), 13, 4), '-',
                        SUBSTR(HEX(suppliers.uuid), 17, 4), '-',
                        SUBSTR(HEX(suppliers.uuid), 21)
                    )) as uuid
                "),
                'suppliers.code',
                'suppliers.name',
                'suppliers.email',
                'suppliers.phone',
                'suppliers.payment_terms',
                'suppliers.balance',
                'suppliers.status',
                'suppliers.created_at'
            ])
            ->whereNull('suppliers.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('suppliers.name', 'like', "%{$search}%")
                  ->orWhere('suppliers.code', 'like', "%{$search}%")
                  ->orWhere('suppliers.email', 'like', "%{$search}%");
            });
        }

        $totalRecords = $query->count();

        $columns = ['code', 'name', 'email', 'phone', 'balance', 'status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('suppliers.created_at');
        }

        $suppliers = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $suppliers
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $suppliers = $this->supplierRepository->search($query);

        return response()->json([
            'results' => $suppliers->map(fn($s) => [
                'id' => $s->id,
                'text' => "{$s->code} - {$s->name}",
                'name' => $s->name
            ])
        ]);
    }
}
