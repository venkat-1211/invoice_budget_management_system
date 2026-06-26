<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return DB::table('customers')
            ->where('code', $code)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getActiveCustomers()
    {
        return DB::table('customers')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function getCustomersWithBalance()
    {
        return DB::table('customers')
            ->select('customers.*')
            ->selectRaw('(SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM sales_invoices WHERE customer_id = customers.id AND status = 1 AND deleted_at IS NULL) as outstanding_balance')
            ->whereNull('customers.deleted_at')
            ->orderByDesc('outstanding_balance')
            ->get();
    }

    public function search(string $query)
    {
        return DB::table('customers')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->get();
    }
}
