<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::prefix('customers')
        ->name('customers.')
        ->controller(CustomerController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{customer}/edit', 'edit')->name('edit');
            Route::put('/{customer}', 'update')->name('update');
            Route::delete('/{customer}', 'destroy')->name('destroy');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/search', 'search')->name('search');
        });

    // Suppliers
    Route::prefix('suppliers')
        ->name('suppliers.')
        ->controller(SupplierController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{supplier}/edit', 'edit')->name('edit');
            Route::put('/{supplier}', 'update')->name('update');
            Route::delete('/{supplier}', 'destroy')->name('destroy');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/search', 'search')->name('search');
        });

    // Products
    Route::prefix('products')
        ->name('products.')
        ->controller(ProductController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{product}/edit', 'edit')->name('edit');
            Route::put('/{product}', 'update')->name('update');
            Route::delete('/{product}', 'destroy')->name('destroy');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/search', 'search')->name('search');
        });

    // Purchase Invoices
    Route::prefix('purchase-invoices')
        ->name('purchase-invoices.')
        ->controller(PurchaseInvoiceController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}', 'show')->name('show');
            Route::get('/{purchaseInvoice}/edit', 'edit')->name('edit');
            Route::put('/{purchaseInvoice}', 'update')->name('update');
            Route::delete('/{purchaseInvoice}', 'destroy')->name('destroy');
            Route::get('/datatable/data', 'datatable')->name('datatable');
        });

    // Sales Invoices
    Route::prefix('sales-invoices')
        ->name('sales-invoices.')
        ->controller(SalesInvoiceController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{uuid}', 'show')->name('show');
            Route::get('/{salesInvoice}/edit', 'edit')->name('edit');
            Route::put('/{salesInvoice}', 'update')->name('update');
            Route::delete('/{salesInvoice}', 'destroy')->name('destroy');
            Route::get('/datatable/data', 'datatable')->name('datatable');
        });

    // Budgets
    Route::prefix('budgets')
        ->name('budgets.')
        ->controller(BudgetController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{budget}/edit', 'edit')->name('edit');
            Route::put('/{budget}', 'update')->name('update');
            Route::delete('/{budget}', 'destroy')->name('destroy');
            Route::get('/datatable/data', 'datatable')->name('datatable');
        });

    // Expenses
    Route::prefix('expenses')
        ->name('expenses.')
        ->controller(ExpenseController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{expense}/edit', 'edit')->name('edit');
            Route::put('/{expense}', 'update')->name('update');
            Route::delete('/{expense}', 'destroy')->name('destroy');
            Route::get('/datatable/data', 'datatable')->name('datatable');
        });

    // Payments
    Route::prefix('payments')
        ->name('payments.')
        ->controller(PaymentController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::delete('/{payment}', 'destroy')->name('destroy');
            Route::get('/datatable/data', 'datatable')->name('datatable');
        });

    // Reports
    Route::prefix('reports')
        ->name('reports.')
        ->controller(ReportController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/revenue', 'revenue')->name('revenue');
            Route::get('/expenses', 'expenses')->name('expenses');
            Route::get('/budgets', 'budgets')->name('budgets');
            Route::get('/profit', 'profit')->name('profit');
        });
});
