<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\EnsureAdministrator;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('welcome');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/suppliers', [HomeController::class, 'suppliers'])->name('suppliers.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::middleware(EnsureAdministrator::class)->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
