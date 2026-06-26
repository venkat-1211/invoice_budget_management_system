<?php

namespace App\Http\Controllers;

use App\DTOs\ProductDTO;
use App\Http\Requests\Product\StoreProductRequest;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function index(): View
    {
        return view('products.index');
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $dto = new ProductDTO(...$request->validated());
        $this->productRepository->create($dto->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(int $id): View
    {
        $product = $this->productRepository->find($id);
        abort_if(!$product, 404);

        return view('products.edit', compact('product'));
    }

    public function update(StoreProductRequest $request, int $id): RedirectResponse
    {
        $dto = new ProductDTO(...$request->validated());
        $this->productRepository->update($id, $dto->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->productRepository->delete($id);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('products')
            ->select([
                'products.id',
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(products.uuid), 1, 8), '-',
                        SUBSTR(HEX(products.uuid), 9, 4), '-',
                        SUBSTR(HEX(products.uuid), 13, 4), '-',
                        SUBSTR(HEX(products.uuid), 17, 4), '-',
                        SUBSTR(HEX(products.uuid), 21)
                    )) as uuid
                "),
                'products.sku',
                'products.name',
                'products.type',
                'products.category',
                'products.unit',
                'products.sale_price',
                'products.stock_quantity',
                'products.min_stock_level',
                'products.status',
                'products.created_at'
            ])
            ->whereNull('products.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('products.category', 'like', "%{$search}%");
            });
        }

        $totalRecords = $query->count();

        $columns = ['sku', 'name', 'type', 'category', 'sale_price', 'stock_quantity', 'status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('products.created_at');
        }

        $products = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $products
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $products = $this->productRepository->search($query);

        return response()->json([
            'results' => $products->map(fn($p) => [
                'id' => $p->id,
                'text' => "{$p->sku} - {$p->name}",
                'name' => $p->name,
                'price' => $p->sale_price,
                'tax_rate' => $p->tax_rate
            ])
        ]);
    }
}
