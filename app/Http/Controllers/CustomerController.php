<?php

namespace App\Http\Controllers;

use App\DTOs\CustomerDTO;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function index(): View
    {
        return view('customers.index');
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $dto = new CustomerDTO(...$request->validated());
        $this->customerRepository->create($dto->toArray());

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully');
    }

    public function edit(Customer $customer): View
    {
        // $customer = $this->customerRepository->find($customer->id);
        // abort_if(!$customer, 404);

        return view('customers.edit', compact('customer'));
    }

    public function update(StoreCustomerRequest $request, int $id): RedirectResponse
    {
        $dto = new CustomerDTO(...$request->validated());
        $this->customerRepository->update($id, $dto->toArray());

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->customerRepository->delete($id);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('customers')
            ->select([
                'customers.id',
                // DB::raw("BIN_TO_UUID(customers.uuid) as uuid"),  // this is useful for mysql8+
                // mariadb (Keela vulla codes podama irukka, namma uuid aa char(36) potta intha conversion devaiye illa.)
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(customers.uuid), 1, 8), '-',
                        SUBSTR(HEX(customers.uuid), 9, 4), '-',
                        SUBSTR(HEX(customers.uuid), 13, 4), '-',
                        SUBSTR(HEX(customers.uuid), 17, 4), '-',
                        SUBSTR(HEX(customers.uuid), 21)
                    )) as uuid
                "),
                'customers.code',
                'customers.name',
                'customers.email',
                'customers.phone',
                'customers.credit_limit',
                'customers.balance',
                'customers.status',
                'customers.created_at'
            ])
            ->whereNull('customers.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');

            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                ->orWhere('customers.code', 'like', "%{$search}%")
                ->orWhere('customers.email', 'like', "%{$search}%");
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
            $query->orderByDesc('customers.created_at');
        }

        $customers = $query
            ->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $customers
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $customers = $this->customerRepository->search($query);

        return response()->json([
            'results' => $customers->map(fn($c) => [
                'id' => $c->id,
                'text' => "{$c->code} - {$c->name}",
                'name' => $c->name
            ])
        ]);
    }
}
