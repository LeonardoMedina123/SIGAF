<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPurchaseOrderDocumentController;
use App\Http\Controllers\ValidatePurchaseOrderPaymentController;
use App\Http\Middleware\EnsureAdministrator;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('welcome');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/suppliers', [HomeController::class, 'suppliers'])->name('suppliers.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/supplier/purchase-orders/{purchaseOrder}/invoices', [SupplierPurchaseOrderDocumentController::class, 'storeInvoice'])
        ->name('supplier.purchase-orders.invoices.store');
    Route::post('/supplier/purchase-orders/{purchaseOrder}/payment-complement', [SupplierPurchaseOrderDocumentController::class, 'storePaymentComplement'])
        ->name('supplier.purchase-orders.payment-complement.store');
    Route::get('/supplier/purchase-orders/{purchaseOrder}/pdf', [SupplierPurchaseOrderDocumentController::class, 'downloadPurchaseOrder'])
        ->name('supplier.purchase-orders.pdf');
    Route::get('/purchase-orders/{purchaseOrder}/documents/{document}', [
        SupplierPurchaseOrderDocumentController::class,
        'downloadInternalDocument',
    ])->name('purchase-orders.documents.download');
    Route::middleware(EnsureAdministrator::class)->post(
        '/admin/purchase-orders/{purchaseOrder}/validate-payment',
        ValidatePurchaseOrderPaymentController::class,
    )->name('admin.purchase-orders.validate-payment');
    Route::middleware(EnsureAdministrator::class)->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
