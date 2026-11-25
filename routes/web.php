<?php

use App\Http\Controllers\AracController;
use App\Http\Controllers\Auth\GirisController;
use App\Http\Controllers\DosyaController;
use App\Http\Controllers\MaliyetController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\SatisController;
use App\Http\Controllers\YonetimController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/giris', [GirisController::class, 'create'])->name('login');
    Route::post('/giris', [GirisController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('panel');
    Route::post('/cikis', [GirisController::class, 'destroy'])->name('logout');

    Route::resource('araclar', AracController::class)
        ->parameters(['araclar' => 'arac'])
        ->names('araclar');

    Route::post('araclar/{arac}/maliyet', [MaliyetController::class, 'store'])->name('maliyet.store');
    Route::delete('araclar/{arac}/maliyet/{maliyet}', [MaliyetController::class, 'destroy'])->name('maliyet.destroy');

    Route::post('araclar/{arac}/satis', [SatisController::class, 'store'])->name('satis.store');
    Route::delete('araclar/{arac}/satis', [SatisController::class, 'destroy'])->name('satis.destroy');

    Route::post('araclar/{arac}/dosya', [DosyaController::class, 'store'])->name('dosya.store');
    Route::get('araclar/{arac}/dosya/{dosya}', [DosyaController::class, 'goster'])->name('dosya.goster');
    Route::delete('araclar/{arac}/dosya/{dosya}', [DosyaController::class, 'destroy'])->name('dosya.destroy');

    Route::middleware('admin')->prefix('yonetim')->name('yonetim.')->group(function () {
        Route::get('/', [YonetimController::class, 'index'])->name('index');
        Route::post('galeri', [YonetimController::class, 'galeriStore'])->name('galeri.store');
        Route::put('galeri/{galeri}', [YonetimController::class, 'galeriUpdate'])->name('galeri.update');
        Route::post('kullanici', [YonetimController::class, 'kullaniciStore'])->name('kullanici.store');
        Route::put('kullanici/{kullanici}', [YonetimController::class, 'kullaniciUpdate'])->name('kullanici.update');
    });
});
