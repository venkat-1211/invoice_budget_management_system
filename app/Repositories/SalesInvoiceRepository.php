<?php

namespace App\Repositories;

use App\Models\SalesInvoice;
use App\Repositories\Interfaces\SalesInvoiceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SalesInvoiceRepository extends BaseRepository implements SalesInvoiceRepositoryInterface
{
    public function __construct(SalesInvoice $model)
    {
        parent::__construct($model);
    }

    public function findByInvoiceNumber(string $number)
    {
        return DB::table('sales_invoices')
            ->where('invoice_number', $number)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getByCustomer(int $customerId)
    {
        return DB::table('sales_invoices')
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->orderByDesc('invoice_date')
            ->get();
    }

    public function getByStatus(string $status)
    {
        return DB::table('sales_invoices')
            ->where('payment_status', $status)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getOverdueInvoices()
    {
        return DB::table('sales_invoices')
            ->where('due_date', '<', now())
            ->whereIn('payment_status', ['pending', 'partial'])
            ->whereNull('deleted_at')
            ->get();
    }

    public function getRecentInvoices(int $limit = 10)
    {
        return DB::table('sales_invoices')
            ->select('sales_invoices.*', 'customers.name as customer_name')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->whereNull('sales_invoices.deleted_at')
            ->orderByDesc('sales_invoices.created_at')
            ->limit($limit)
            ->get();
    }

    public function getTotalsByPeriod(string $startDate, string $endDate)
    {
        return DB::table('sales_invoices')
            ->selectRaw('DATE(invoice_date) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->groupBy('date')
            ->get();
    }
}
