<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentDTO;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Services\InvoiceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private InvoiceNumberService $invoiceNumberService
    ) {}

    public function index(): View
    {
        return view('payments.index');
    }

    public function create(Request $request): View
    {
        $invoiceType = $request->get('type', 'sales');
        $invoiceId = $request->get('invoice_id');

        $invoice = null;
        if ($invoiceId) {
            $invoice = $invoiceType === 'sales'
                ? SalesInvoice::find($invoiceId)
                : PurchaseInvoice::find($invoiceId);
        }

        $salesInvoices = SalesInvoice::whereIn('payment_status', ['pending', 'partial', 'overdue'])
            ->with('customer')
            ->get();
        $purchaseInvoices = PurchaseInvoice::whereIn('payment_status', ['pending', 'partial', 'overdue'])
            ->with('supplier')
            ->get();

        return view('payments.create', compact('invoice', 'invoiceType', 'salesInvoices', 'purchaseInvoices'));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $this->checkRateLimit('payment-create');

        DB::transaction(function () use ($request) {
            $paymentNumber = $this->invoiceNumberService->generatePaymentNumber();

            $dto = new PaymentDTO(
                payment_number: $paymentNumber,
                payable_type: $request->payable_type,
                payable_id: $request->payable_id,
                payment_date: $request->payment_date,
                amount: $request->amount,
                payment_method: $request->payment_method,
                reference: $request->reference,
                transaction_id: $request->transaction_id,
                notes: $request->notes,
                created_by: Auth::id(),
                status: 1
            );

            $this->paymentRepository->create($dto->toArray());
        });

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->paymentRepository->delete($id);

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = DB::table('payments')
            ->select([
                'payments.id',
                'payments.uuid',
                'payments.payment_number',
                'payments.payable_type',
                'payments.payable_id',
                'payments.payment_date',
                'payments.amount',
                'payments.payment_method',
                'payments.reference',
                'payments.created_at'
            ])
            ->whereNull('payments.deleted_at');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('payments.payment_number', 'like', "%{$search}%")
                  ->orWhere('payments.reference', 'like', "%{$search}%");
            });
        }

        $totalRecords = $query->count();

        $columns = ['payment_number', 'payment_date', 'amount', 'payment_method', 'reference', 'created_at'];
        if ($request->filled('order.0.column') && isset($columns[$request->input('order.0.column')])) {
            $query->orderBy(
                $columns[$request->input('order.0.column')],
                $request->input('order.0.dir', 'asc')
            );
        } else {
            $query->orderByDesc('payments.created_at');
        }

        $payments = $query->offset($request->input('start', 0))
            ->limit($request->input('length', 10))
            ->get();

        // Enhance with invoice references
        foreach ($payments as $payment) {
            if ($payment->payable_type === 'App\\Models\\SalesInvoice') {
                $payment->invoice_ref = DB::table('sales_invoices')
                    ->where('id', $payment->payable_id)
                    ->value('invoice_number');
            } else {
                $payment->invoice_ref = DB::table('purchase_invoices')
                    ->where('id', $payment->payable_id)
                    ->value('invoice_number');
            }
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $payments
        ]);
    }

    private function checkRateLimit(string $key): void
    {
        if (RateLimiter::tooManyAttempts($key . '|' . request()->ip(), 15)) {
            abort(429, 'Too many payment attempts. Please try again later.');
        }
        RateLimiter::hit($key . '|' . request()->ip());
    }
}
