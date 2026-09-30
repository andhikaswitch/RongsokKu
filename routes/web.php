<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pengepul;
use App\Http\Controllers\Publik;
use App\Http\Controllers\SegeraHadirController;
use App\Http\Controllers\Warga;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
| Dapat diakses tanpa login. Halaman Pusat Harga sengaja dibuka untuk umum
| agar bisa dipakai sebagai alat sosialisasi ke warga lewat RT/RW.
*/

Route::get('/', Publik\BerandaController::class)->name('beranda');
Route::post('/tema', [Publik\BerandaController::class, 'gantiTema'])->name('tema.ganti');

Route::get('/harga', [Publik\HargaController::class, 'index'])->name('harga.index');
Route::get('/harga/{kategori}', [Publik\HargaController::class, 'show'])->name('harga.show');

Route::get('/pengepul', [Publik\PengepulPublikController::class, 'index'])->name('pengepul.cari');
Route::get('/pengepul/{pengepul}', [Publik\PengepulPublikController::class, 'show'])->name('pengepul.detail');

Route::get('/peringkat', [Publik\HalamanController::class, 'peringkat'])->name('peringkat');
Route::get('/cara-kerja', [Publik\HalamanController::class, 'caraKerja'])->name('cara-kerja');
Route::get('/tentang', [Publik\HalamanController::class, 'tentang'])->name('tentang');
Route::get('/faq', [Publik\HalamanController::class, 'faq'])->name('faq');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'formRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register']);
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Panel Warga
|--------------------------------------------------------------------------
| Penanggung jawab: Muhammad Rizky Rajabi (2410631170039)
*/

Route::middleware(['auth', 'peran:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/', [Warga\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dampak', [Warga\DashboardController::class, 'dampak'])->name('dampak');

    Route::get('/permintaan', SegeraHadirController::class)
        ->defaults('judul', 'Permintaan Saya')
        ->defaults('penanggungJawab', 'Rizky (2410631170039)')
        ->name('permintaan.index');

    Route::get('/ajukan', SegeraHadirController::class)
        ->defaults('judul', 'Ajukan Penjemputan')
        ->defaults('penanggungJawab', 'Rizky (2410631170039)')
        ->name('ajukan');

    Route::get('/profil', SegeraHadirController::class)
        ->defaults('judul', 'Profil Saya')
        ->defaults('penanggungJawab', 'Rizky (2410631170039)')
        ->name('profil');
});

/*
|--------------------------------------------------------------------------
| Panel Pengepul
|--------------------------------------------------------------------------
| Penanggung jawab: Defry Ananta Perangin Angin (2410631170066)
*/

Route::middleware(['auth', 'peran:pengepul'])->prefix('mitra')->name('pengepul.')->group(function () {
    Route::get('/', [Pengepul\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/performa', [Pengepul\DashboardController::class, 'performa'])->name('performa');

    Route::get('/verifikasi', SegeraHadirController::class)
        ->defaults('judul', 'Berkas Verifikasi')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('verifikasi.form');

    Route::get('/verifikasi/status', SegeraHadirController::class)
        ->defaults('judul', 'Status Verifikasi')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('verifikasi.status');

    Route::get('/harga', SegeraHadirController::class)
        ->defaults('judul', 'Daftar Harga Saya')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('harga.index');

    Route::get('/permintaan', SegeraHadirController::class)
        ->defaults('judul', 'Permintaan Masuk')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('permintaan.index');

    Route::get('/terbuka', SegeraHadirController::class)
        ->defaults('judul', 'Permintaan Terbuka')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('terbuka.index');

    Route::get('/dompet', SegeraHadirController::class)
        ->defaults('judul', 'Dompet & Saldo')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('dompet.index');

    Route::get('/profil', SegeraHadirController::class)
        ->defaults('judul', 'Profil Lapak')
        ->defaults('penanggungJawab', 'Defry (2410631170066)')
        ->name('profil');
});

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
| Penanggung jawab: Diego Andreas Simanjuntak (2410631170068)
*/

Route::middleware(['auth', 'peran:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    $modul = [
        'verifikasi.index' => ['verifikasi', 'Verifikasi Pengepul'],
        'topup.index' => ['topup', 'Verifikasi Top-up'],
        'transaksi.index' => ['transaksi', 'Pantau Transaksi'],
        'sengketa.index' => ['sengketa', 'Penanganan Sengketa'],
        'kategori.index' => ['kategori', 'Kategori Sampah'],
        'harga.index' => ['harga', 'Harga Acuan & Indeks'],
        'pengguna.index' => ['pengguna', 'Kelola Pengguna'],
        'pengaturan' => ['pengaturan', 'Pengaturan Platform'],
    ];

    foreach ($modul as $nama => [$path, $judul]) {
        Route::get("/$path", SegeraHadirController::class)
            ->defaults('judul', $judul)
            ->defaults('penanggungJawab', 'Diego (2410631170068)')
            ->name($nama);
    }
});
