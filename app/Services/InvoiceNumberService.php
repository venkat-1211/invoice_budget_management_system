<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function generatePurchaseInvoiceNumber(): string
    {
        $prefix = 'PI-' . now()->format('Y');
        $last = DB::table('purchase_invoices')
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = $last ? (int) substr($last, strlen($prefix) + 1) + 1 : 1;
        return $prefix . '-' . str_pad((string)$sequence, 5, '0', STR_PAD_LEFT);
    }

    public function generateSalesInvoiceNumber(): string
    {
        $prefix = 'SI-' . now()->format('Y');
        $last = DB::table('sales_invoices')
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = $last ? (int) substr($last, strlen($prefix) + 1) + 1 : 1;
        return $prefix . '-' . str_pad((string)$sequence, 5, '0', STR_PAD_LEFT);
    }

    public function generateExpenseNumber(): string
    {
        $prefix = 'EXP-' . now()->format('Y');
        $last = DB::table('expenses')
            ->where('expense_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('expense_number');

        $sequence = $last ? (int) substr($last, strlen($prefix) + 1) + 1 : 1;
        return $prefix . '-' . str_pad((string)$sequence, 5, '0', STR_PAD_LEFT);
    }

    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . now()->format('Ymd');
        $last = DB::table('payments')
            ->where('payment_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('payment_number');

        $sequence = $last ? (int) substr($last, strlen($prefix) + 1) + 1 : 1;
        return $prefix . '-' . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
