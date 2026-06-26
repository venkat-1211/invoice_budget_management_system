<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return DB::table('suppliers')
            ->where('code', $code)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getActiveSuppliers()
    {
        return DB::table('suppliers')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function getSuppliersWithBalance()
    {
        return DB::table('suppliers')
            ->select('suppliers.*')
            ->selectRaw('(SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM purchase_invoices WHERE supplier_id = suppliers.id AND status = 1 AND deleted_at IS NULL) as outstanding_balance')
            ->whereNull('suppliers.deleted_at')
            ->orderByDesc('outstanding_balance')
            ->get();
    }

    public function search(string $query)
    {
        return DB::table('suppliers')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->get();
    }
}
