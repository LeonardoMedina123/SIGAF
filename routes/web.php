<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('welcome');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('authenticate');
Route::view('/register', 'register')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::middleware('auth')->group(function () {
    Route::view('/home', 'home')->name('home');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
