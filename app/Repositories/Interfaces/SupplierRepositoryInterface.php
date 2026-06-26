<?php

namespace App\Repositories\Interfaces;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveSuppliers();
    public function getSuppliersWithBalance();
    public function search(string $query);
}
