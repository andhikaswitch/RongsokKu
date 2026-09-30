<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Pengepul;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Publik;
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
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'formRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // Berkas pribadi, hak aksesnya diperiksa per berkas di controller.
    Route::prefix('berkas')->name('berkas.')->group(function () {
        Route::get('/barang/{item}', [BerkasController::class, 'fotoBarang'])->name('barang');
        Route::get('/topup/{topup}', [BerkasController::class, 'buktiTopup'])->name('topup');
        Route::get('/ktp/{pengepul}', [BerkasController::class, 'ktp'])->name('ktp');
        Route::get('/sengketa/{sengketa}', [BerkasController::class, 'buktiSengketa'])->name('sengketa');
    });
});

/*
|--------------------------------------------------------------------------
| Panel Warga
|--------------------------------------------------------------------------
| Penanggung jawab: Muhammad Rizky Rajabi (2410631170039)
*/

Route::middleware(['auth', 'peran:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/', [Warga\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dampak', [Warga\DashboardController::class, 'dampak'])->name('dampak');

    // Wizard pengajuan penjemputan
    Route::get('/ajukan', [Warga\PengajuanController::class, 'mulai'])->name('ajukan');
    Route::get('/ajukan/barang', [Warga\PengajuanController::class, 'barang'])->name('ajukan.barang');
    Route::post('/ajukan/barang', [Warga\PengajuanController::class, 'tambahBarang'])->name('ajukan.barang.tambah');
    Route::delete('/ajukan/barang/{indeks}', [Warga\PengajuanController::class, 'hapusBarang'])
        ->whereNumber('indeks')->name('ajukan.barang.hapus');
    Route::get('/ajukan/jadwal', [Warga\PengajuanController::class, 'jadwal'])->name('ajukan.jadwal');
    Route::post('/ajukan', [Warga\PengajuanController::class, 'kirim'])->name('ajukan.kirim');
    Route::delete('/ajukan', [Warga\PengajuanController::class, 'batal'])->name('ajukan.batal');

    // Permintaan saya
    Route::get('/permintaan', [Warga\PermintaanController::class, 'index'])->name('permintaan.index');
    Route::get('/permintaan/{permintaan}', [Warga\PermintaanController::class, 'show'])->name('permintaan.show');
    Route::post('/permintaan/{permintaan}/batal', [Warga\PermintaanController::class, 'batal'])->name('permintaan.batal');
    Route::post('/permintaan/{permintaan}/konfirmasi', [Warga\PermintaanController::class, 'konfirmasi'])->name('permintaan.konfirmasi');
    Route::post('/permintaan/{permintaan}/sengketa', [Warga\PermintaanController::class, 'sengketa'])->name('permintaan.sengketa');
    Route::post('/permintaan/{permintaan}/ulasan', [Warga\PermintaanController::class, 'ulasan'])->name('permintaan.ulasan');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/sandi', [ProfilController::class, 'sandi'])->name('profil.sandi');
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
    Route::post('/ulasan/{ulasan}/balas', [Pengepul\LapakController::class, 'balasUlasan'])->name('ulasan.balas');

    Route::get('/verifikasi', [Pengepul\VerifikasiController::class, 'form'])->name('verifikasi.form');
    Route::post('/verifikasi', [Pengepul\VerifikasiController::class, 'ajukan'])->name('verifikasi.ajukan');
    Route::get('/verifikasi/status', [Pengepul\VerifikasiController::class, 'status'])->name('verifikasi.status');

    // Dompet boleh diakses sebelum terverifikasi agar bisa mengisi saldo lebih awal.
    Route::get('/dompet', [Pengepul\DompetController::class, 'index'])->name('dompet.index');
    Route::get('/dompet/isi', [Pengepul\DompetController::class, 'formTopup'])->name('dompet.topup');
    Route::post('/dompet/isi', [Pengepul\DompetController::class, 'topup'])->name('dompet.topup.kirim');

    Route::get('/lapak', [Pengepul\LapakController::class, 'edit'])->name('profil');
    Route::put('/lapak', [Pengepul\LapakController::class, 'update'])->name('profil.update');
    Route::get('/akun', [ProfilController::class, 'edit'])->name('akun');
    Route::put('/akun', [ProfilController::class, 'update'])->name('akun.update');
    Route::put('/akun/sandi', [ProfilController::class, 'sandi'])->name('akun.sandi');

    Route::middleware('pengepul.terverifikasi')->group(function () {
        Route::get('/harga', [Pengepul\HargaController::class, 'index'])->name('harga.index');
        Route::put('/harga', [Pengepul\HargaController::class, 'simpan'])->name('harga.simpan');

        Route::get('/permintaan', [Pengepul\PermintaanController::class, 'index'])->name('permintaan.index');
        Route::get('/permintaan/{permintaan}', [Pengepul\PermintaanController::class, 'show'])->name('permintaan.show');
        Route::post('/permintaan/{permintaan}/terima', [Pengepul\PermintaanController::class, 'terima'])
            ->middleware('saldo.cukup')->name('permintaan.terima');
        Route::post('/permintaan/{permintaan}/tolak', [Pengepul\PermintaanController::class, 'tolak'])->name('permintaan.tolak');
        Route::post('/permintaan/{permintaan}/berangkat', [Pengepul\PermintaanController::class, 'berangkat'])->name('permintaan.berangkat');
        Route::post('/permintaan/{permintaan}/timbang', [Pengepul\PermintaanController::class, 'timbang'])->name('permintaan.timbang');
        Route::post('/permintaan/{permintaan}/batal', [Pengepul\PermintaanController::class, 'batal'])->name('permintaan.batal');

        Route::get('/terbuka', [Pengepul\TerbukaController::class, 'index'])->name('terbuka.index');
        Route::post('/terbuka/{permintaan}/klaim', [Pengepul\TerbukaController::class, 'klaim'])
            ->middleware('saldo.cukup')->name('terbuka.klaim');
    });
});

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
| Penanggung jawab: Diego Andreas Simanjuntak (2410631170068)
*/

Route::middleware(['auth', 'peran:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/verifikasi', [Admin\VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::get('/verifikasi/{pengepul}', [Admin\VerifikasiController::class, 'show'])->name('verifikasi.show');
    Route::post('/verifikasi/{pengepul}/setujui', [Admin\VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
    Route::post('/verifikasi/{pengepul}/tolak', [Admin\VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

    Route::get('/topup', [Admin\TopupController::class, 'index'])->name('topup.index');
    Route::get('/topup/{topup}', [Admin\TopupController::class, 'show'])->name('topup.show');
    Route::post('/topup/{topup}/setujui', [Admin\TopupController::class, 'setujui'])->name('topup.setujui');
    Route::post('/topup/{topup}/tolak', [Admin\TopupController::class, 'tolak'])->name('topup.tolak');

    Route::get('/transaksi', [Admin\TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/ekspor', [Admin\TransaksiController::class, 'ekspor'])->name('transaksi.ekspor');
    Route::get('/transaksi/{permintaan}', [Admin\TransaksiController::class, 'show'])->name('transaksi.show');

    Route::get('/sengketa', [Admin\SengketaController::class, 'index'])->name('sengketa.index');
    Route::get('/sengketa/{sengketa}', [Admin\SengketaController::class, 'show'])->name('sengketa.show');
    Route::post('/sengketa/{sengketa}/putuskan', [Admin\SengketaController::class, 'putuskan'])->name('sengketa.putuskan');

    Route::resource('kategori', Admin\KategoriController::class)
        ->except('show')
        ->parameters(['kategori' => 'kategori']);

    Route::get('/harga', [Admin\HargaController::class, 'index'])->name('harga.index');
    Route::post('/harga/hitung-ulang', [Admin\HargaController::class, 'hitungUlang'])->name('harga.hitung-ulang');
    Route::get('/harga/acuan/baru', [Admin\HargaController::class, 'create'])->name('harga.create');
    Route::post('/harga/acuan', [Admin\HargaController::class, 'store'])->name('harga.store');
    Route::get('/harga/acuan/{sumber}', [Admin\HargaController::class, 'edit'])->name('harga.edit');
    Route::put('/harga/acuan/{sumber}', [Admin\HargaController::class, 'update'])->name('harga.update');
    Route::delete('/harga/acuan/{sumber}', [Admin\HargaController::class, 'destroy'])->name('harga.destroy');

    Route::get('/pengguna', [Admin\PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{pengguna}', [Admin\PenggunaController::class, 'show'])->name('pengguna.show');
    Route::post('/pengguna/{pengguna}/nonaktifkan', [Admin\PenggunaController::class, 'nonaktifkan'])->name('pengguna.nonaktifkan');
    Route::post('/pengguna/{pengguna}/aktifkan', [Admin\PenggunaController::class, 'aktifkan'])->name('pengguna.aktifkan');
    Route::post('/pengguna/{pengguna}/atur-ulang-sandi', [Admin\PenggunaController::class, 'aturUlangSandi'])->name('pengguna.sandi');

    Route::get('/pengaturan', [Admin\PengaturanController::class, 'edit'])->name('pengaturan');
    Route::put('/pengaturan', [Admin\PengaturanController::class, 'update'])->name('pengaturan.update');
    Route::get('/log', [Admin\PengaturanController::class, 'log'])->name('log');
});
