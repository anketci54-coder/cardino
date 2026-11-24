<?php

use App\Http\Controllers\AracController;
use App\Http\Controllers\Auth\GirisController;
use App\Http\Controllers\MaliyetController;
use App\Http\Controllers\SatisController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/giris', [GirisController::class, 'create'])->name('login');
    Route::post('/giris', [GirisController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('panel'))->name('panel');
    Route::post('/cikis', [GirisController::class, 'destroy'])->name('logout');

    Route::resource('araclar', AracController::class)
        ->parameters(['araclar' => 'arac'])
        ->names('araclar');

    Route::post('araclar/{arac}/maliyet', [MaliyetController::class, 'store'])->name('maliyet.store');
    Route::delete('araclar/{arac}/maliyet/{maliyet}', [MaliyetController::class, 'destroy'])->name('maliyet.destroy');

    Route::post('araclar/{arac}/satis', [SatisController::class, 'store'])->name('satis.store');
    Route::delete('araclar/{arac}/satis', [SatisController::class, 'destroy'])->name('satis.destroy');
});
