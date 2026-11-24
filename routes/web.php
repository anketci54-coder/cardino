<?php

use App\Http\Controllers\Auth\GirisController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/giris', [GirisController::class, 'create'])->name('login');
    Route::post('/giris', [GirisController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('panel'))->name('panel');
    Route::post('/cikis', [GirisController::class, 'destroy'])->name('logout');
});
