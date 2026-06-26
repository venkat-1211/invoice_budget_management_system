<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySku(string $sku);
    public function getActiveProducts();
    public function getLowStockProducts();
    public function search(string $query);
    public function getByCategory(string $category);
}
