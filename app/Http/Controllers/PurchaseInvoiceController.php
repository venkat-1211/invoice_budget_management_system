<?php

namespace App\Http\Controllers;

use App\DTOs\PurchaseInvoiceDTO;
use App\DTOs\PurchaseInvoiceItemDTO;
use App\Http\Requests\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Services\InvoiceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        private PurchaseInvoiceRepositoryInterface $purchaseInvoiceRepo,
        private SupplierRepositoryInterface $supplierRepo,
        private ProductRepositoryInterface $productRepo,
        private InvoiceNumberService $invoiceNumberService
    ) {}

    public function index(): View
    {
        return view('purchase-invoices.index');
    }

    public function create(): View
    {
        $suppliers = $this->supplierRepo->getActiveSuppliers();
        $products = $this->productRepo->getActiveProducts();

        return view('purchase-invoices.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseInvoiceRequest $request): RedirectResponse
    {
        $this->checkRateLimit('purchase-invoice-create');

        DB::transaction(function () use ($request) {
            $invoiceNumber = $this->invoiceNumberService->generatePurchaseInvoiceNumber();

            $dto = new PurchaseInvoiceDTO(
                invoice_number: $invoiceNumber,
                supplier_id: $request->supplier_id,
                invoice_date: $request->invoice_date,
                due_date: $request->due_date,
                currency: $request->currency,
                exchange_rate: $request->exchange_rate ?? 1,
                reference: $request->reference,
                notes: $request->notes,
                created_by: Auth::id(),
                status: 1,
                items: $request->items
            );

            $invoice = PurchaseInvoice::create($dto->toArray());

            foreach ($request->items as $item) {
                $itemDto = new PurchaseInvoiceItemDTO(
                    purchase_invoice_id: $invoice->id,
                    product_id: $item['product_id'] ?? null,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unit: $item['unit'],
                    unit_price: $item['unit_price'],
                    tax_rate: $item['tax_rate'] ?? 0,
                    discount_percent: $item['discount_percent'] ?? 0
                );
                PurchaseInvoiceItem::create($itemDto->toArray());
            }

            $invoice->recalculateTotals();
            $invoice->supplier->updateBalance();
        });

        return redirect()->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice created successfully');
    }

    public function show(string $uuid): View
    {
        $invoice = PurchaseInvoice::byUuid($uuid)
            ->with(['supplier', 'items.product', 'payments', 'creator'])
            ->firstOrFail();

        return view('purchase-invoices.show', compact('invoice'));
    }

    public function edit(int $id): View
    {
        $invoice = PurchaseInvoice::with(['items', 'supplier'])->findOrFail($id);
        $suppliers = $this->supplierRepo->getActiveSuppliers();
        $products = $this->productRepo->getActiveProducts();

        return view('purchase-invoices.edit', compact('invoice', 'suppliers', 'products'));
    }

    public function update(StorePurchaseInvoiceRequest $request, int $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id) {
            $invoice = PurchaseInvoice::findOrFail($id);

            $dto = new PurchaseInvoiceDTO(
                invoice_number: $invoice->invoice_number,
                supplier_id: $request->supplier_id,
                invoice_date: $request->invoice_date,
                due_date: $request->due_date,
                currency: $request->currency,
                exchange_rate: $request->exchange_rate ?? 1,
                reference: $request->reference,
                notes: $request->notes,
                status: 1
            );

            $invoice->update($dto->toArray());
            $invoice->items()->delete();

            foreach ($request->items as $item) {
                $itemDto = new PurchaseInvoiceItemDTO(
                    purchase_invoice_id: $invoice->id,
                    product_id: $item['product_id'] ?? null,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unit: $item['unit'],
                    unit_price: $item['unit_price'],
                    tax_rate: $item['tax_rate'] ?? 0,
                    discount_percent: $item['discount_percent'] ?? 0
                );
                PurchaseInvoiceItem::create($itemDto->toArray());
            }

            $invoice->recalculateTotals();
            $invoice->supplier->updateBalance();
        });

        return redirect()->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $invoice = PurchaseInvoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('purchase_invoices')
            ->select([
                'purchase_invoices.id',
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(purchase_invoices.uuid), 1, 8), '-',
                        SUBSTR(HEX(purchase_invoices.uuid), 9, 4), '-',
                        SUBSTR(HEX(purchase_invoices.uuid), 13, 4), '-',
                        SUBSTR(HEX(purchase_invoices.uuid), 17, 4), '-',
                        SUBSTR(HEX(purchase_invoices.uuid), 21)
                    )) as uuid
                "),
                'purchase_invoices.invoice_number',
                'suppliers.name as supplier_name',
                'purchase_invoices.invoice_date',
                'purchase_invoices.due_date',
                'purchase_invoices.total_amount',
                'purchase_invoices.paid_amount',
                'purchase_invoices.payment_status',
                'purchase_invoices.currency',
                'purchase_invoices.created_at'
            ])
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->whereNull('purchase_invoices.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('purchase_invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('purchase_invoices.payment_status', $request->status);
        }

        $totalRecords = $query->count();

        $columns = ['invoice_number', 'supplier_name', 'invoice_date', 'due_date', 'total_amount', 'payment_status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('purchase_invoices.created_at');
        }

        $invoices = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $invoices
        ]);
    }

    private function checkRateLimit(string $key): void
    {
        if (RateLimiter::tooManyAttempts($key . '|' . request()->ip(), 10)) {
            abort(429, 'Too many attempts. Please try again later.');
        }
        RateLimiter::hit($key . '|' . request()->ip());
    }
}
