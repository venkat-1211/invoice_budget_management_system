<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function findByPaymentNumber(string $number)
    {
        return DB::table('payments')
            ->where('payment_number', $number)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getByPayable(string $type, int $id)
    {
        return DB::table('payments')
            ->where('payable_type', $type)
            ->where('payable_id', $id)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate)
    {
        return DB::table('payments')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->get();
    }

    public function getRecentPayments(int $limit = 10)
    {
        return DB::table('payments')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
