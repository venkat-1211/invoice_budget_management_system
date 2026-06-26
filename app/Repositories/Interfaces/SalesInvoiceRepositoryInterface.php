<?php

namespace App\Repositories\Interfaces;

interface SalesInvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByInvoiceNumber(string $number);
    public function getByCustomer(int $customerId);
    public function getByStatus(string $status);
    public function getOverdueInvoices();
    public function getRecentInvoices(int $limit = 10);
    public function getTotalsByPeriod(string $startDate, string $endDate);
}
