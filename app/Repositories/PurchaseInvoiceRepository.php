<?php

namespace App\Repositories;

use App\Models\PurchaseInvoice;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceRepository extends BaseRepository implements PurchaseInvoiceRepositoryInterface
{
    public function __construct(PurchaseInvoice $model)
    {
        parent::__construct($model);
    }

    public function findByInvoiceNumber(string $number)
    {
        return DB::table('purchase_invoices')
            ->where('invoice_number', $number)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getBySupplier(int $supplierId)
    {
        return DB::table('purchase_invoices')
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->orderByDesc('invoice_date')
            ->get();
    }

    public function getByStatus(string $status)
    {
        return DB::table('purchase_invoices')
            ->where('payment_status', $status)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getOverdueInvoices()
    {
        return DB::table('purchase_invoices')
            ->where('due_date', '<', now())
            ->whereIn('payment_status', ['pending', 'partial'])
            ->whereNull('deleted_at')
            ->get();
    }

    public function getRecentInvoices(int $limit = 10)
    {
        return DB::table('purchase_invoices')
            ->select('purchase_invoices.*', 'suppliers.name as supplier_name')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->whereNull('purchase_invoices.deleted_at')
            ->orderByDesc('purchase_invoices.created_at')
            ->limit($limit)
            ->get();
    }

    public function getTotalsByPeriod(string $startDate, string $endDate)
    {
        return DB::table('purchase_invoices')
            ->selectRaw('DATE(invoice_date) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->groupBy('date')
            ->get();
    }
}
