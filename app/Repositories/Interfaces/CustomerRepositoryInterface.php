<?php

namespace App\Repositories\Interfaces;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveCustomers();
    public function getCustomersWithBalance();
    public function search(string $query);
}
