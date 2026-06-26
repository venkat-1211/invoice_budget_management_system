<?php

namespace App\Repositories\Interfaces;

interface PurchaseInvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByInvoiceNumber(string $number);
    public function getBySupplier(int $supplierId);
    public function getByStatus(string $status);
    public function getOverdueInvoices();
    public function getRecentInvoices(int $limit = 10);
    public function getTotalsByPeriod(string $startDate, string $endDate);
}
