<?php

namespace App\Repositories\Interfaces;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPaymentNumber(string $number);
    public function getByPayable(string $type, int $id);
    public function getByDateRange(string $startDate, string $endDate);
    public function getRecentPayments(int $limit = 10);
}
