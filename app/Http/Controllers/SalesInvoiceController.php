<?php

namespace App\Http\Controllers;

use App\DTOs\SalesInvoiceDTO;
use App\DTOs\SalesInvoiceItemDTO;
use App\Http\Requests\SalesInvoice\StoreSalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\SalesInvoiceRepositoryInterface;
use App\Services\InvoiceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    public function __construct(
        private SalesInvoiceRepositoryInterface $salesInvoiceRepo,
        private CustomerRepositoryInterface $customerRepo,
        private ProductRepositoryInterface $productRepo,
        private InvoiceNumberService $invoiceNumberService
    ) {}

    public function index(): View
    {
        return view('sales-invoices.index');
    }

    public function create(): View
    {
        $customers = $this->customerRepo->getActiveCustomers();
        $products = $this->productRepo->getActiveProducts();

        return view('sales-invoices.create', compact('customers', 'products'));
    }

    public function store(StoreSalesInvoiceRequest $request): RedirectResponse
    {
        $this->checkRateLimit('sales-invoice-create');

        DB::transaction(function () use ($request) {
            $invoiceNumber = $this->invoiceNumberService->generateSalesInvoiceNumber();

            $dto = new SalesInvoiceDTO(
                invoice_number: $invoiceNumber,
                customer_id: $request->customer_id,
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

            $invoice = SalesInvoice::create($dto->toArray());

            foreach ($request->items as $item) {
                $itemDto = new SalesInvoiceItemDTO(
                    sales_invoice_id: $invoice->id,
                    product_id: $item['product_id'] ?? null,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unit: $item['unit'],
                    unit_price: $item['unit_price'],
                    tax_rate: $item['tax_rate'] ?? 0,
                    discount_percent: $item['discount_percent'] ?? 0
                );
                SalesInvoiceItem::create($itemDto->toArray());
            }

            $invoice->recalculateTotals();
            $invoice->customer->updateBalance();
        });

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Sales invoice created successfully');
    }

    public function show(string $uuid): View
    {
        $invoice = SalesInvoice::byUuid($uuid)
            ->with(['customer', 'items.product', 'payments', 'creator'])
            ->firstOrFail();

        return view('sales-invoices.show', compact('invoice'));
    }

    public function edit(int $id): View
    {
        $invoice = SalesInvoice::with(['items', 'customer'])->findOrFail($id);
        $customers = $this->customerRepo->getActiveCustomers();
        $products = $this->productRepo->getActiveProducts();

        return view('sales-invoices.edit', compact('invoice', 'customers', 'products'));
    }

    public function update(StoreSalesInvoiceRequest $request, int $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id) {
            $invoice = SalesInvoice::findOrFail($id);

            $dto = new SalesInvoiceDTO(
                invoice_number: $invoice->invoice_number,
                customer_id: $request->customer_id,
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
                $itemDto = new SalesInvoiceItemDTO(
                    sales_invoice_id: $invoice->id,
                    product_id: $item['product_id'] ?? null,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unit: $item['unit'],
                    unit_price: $item['unit_price'],
                    tax_rate: $item['tax_rate'] ?? 0,
                    discount_percent: $item['discount_percent'] ?? 0
                );
                SalesInvoiceItem::create($itemDto->toArray());
            }

            $invoice->recalculateTotals();
            $invoice->customer->updateBalance();
        });

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Sales invoice updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $invoice = SalesInvoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('sales-invoices.index')
            ->with('success', 'Sales invoice deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('sales_invoices')
            ->select([
                'sales_invoices.id',
                DB::raw("
                    LOWER(CONCAT(
                        SUBSTR(HEX(customers.uuid), 1, 8), '-',
                        SUBSTR(HEX(customers.uuid), 9, 4), '-',
                        SUBSTR(HEX(customers.uuid), 13, 4), '-',
                        SUBSTR(HEX(customers.uuid), 17, 4), '-',
                        SUBSTR(HEX(customers.uuid), 21)
                    )) as uuid
                "),
                'sales_invoices.invoice_number',
                'customers.name as customer_name',
                'sales_invoices.invoice_date',
                'sales_invoices.due_date',
                'sales_invoices.total_amount',
                'sales_invoices.paid_amount',
                'sales_invoices.payment_status',
                'sales_invoices.currency',
                'sales_invoices.created_at'
            ])
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->whereNull('sales_invoices.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('sales_invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('sales_invoices.payment_status', $request->status);
        }

        $totalRecords = $query->count();

        $columns = ['invoice_number', 'customer_name', 'invoice_date', 'due_date', 'total_amount', 'payment_status', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('sales_invoices.created_at');
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
