<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Pelanggan\AkunController;
use App\Http\Controllers\Pelanggan\AuthController as PelangganAuthController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'index'])->name('shop.home');
Route::get('/keranjang', [ShopController::class, 'cart'])->name('cart.index');
Route::post('/keranjang', [ShopController::class, 'addToCart'])->name('cart.add');
Route::delete('/keranjang/{product}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout.show');
Route::post('/checkout', [ShopController::class, 'placeOrder'])->name('checkout.place');
Route::get('/pesanan/{kode}', [ShopController::class, 'order'])->name('orders.show');

Route::prefix('masuk')->group(function () {
    Route::middleware('guest:pelanggan')->group(function () {
        Route::get('/', [PelangganAuthController::class, 'showLogin'])->name('pelanggan.login');
        Route::post('/', [PelangganAuthController::class, 'login'])->name('pelanggan.login.attempt');
        Route::get('/daftar', [PelangganAuthController::class, 'showRegister'])->name('pelanggan.daftar');
        Route::post('/daftar', [PelangganAuthController::class, 'register'])->name('pelanggan.daftar.attempt');
    });

    Route::post('/keluar', [PelangganAuthController::class, 'logout'])
        ->middleware('auth:pelanggan')
        ->name('pelanggan.logout');
});

Route::middleware('auth:pelanggan')->prefix('akun')->name('akun.')->group(function () {
    Route::get('/', [AkunController::class, 'index'])->name('index');
    Route::post('/pesan/{order}/ulang', [AkunController::class, 'pesanUlang'])->name('pesan-ulang');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('masuk', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('masuk', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('keluar', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/{order}', [PesananController::class, 'show'])->name('pesanan.show');
        Route::put('pesanan/{order}', [PesananController::class, 'update'])->name('pesanan.update');

        Route::get('produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('produk/baru', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('produk/{product}/ubah', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('produk/{product}', [ProdukController::class, 'update'])->name('produk.update');
        Route::post('produk/{product}/grosir', [ProdukController::class, 'storeTier'])->name('produk.grosir.store');
        Route::delete('produk/{product}/grosir/{tier}', [ProdukController::class, 'destroyTier'])->name('produk.grosir.destroy');

        Route::get('kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('kategori/{category}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('kategori/{category}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });
});
