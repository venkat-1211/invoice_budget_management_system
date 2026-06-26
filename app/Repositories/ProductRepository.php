<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySku(string $sku)
    {
        return DB::table('products')
            ->where('sku', $sku)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getActiveProducts()
    {
        return DB::table('products')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function getLowStockProducts()
    {
        return DB::table('products')
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->whereNull('deleted_at')
            ->get();
    }

    public function search(string $query)
    {
        return DB::table('products')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->get();
    }

    public function getByCategory(string $category)
    {
        return DB::table('products')
            ->where('category', $category)
            ->whereNull('deleted_at')
            ->get();
    }
}
