<?php

namespace App\Http\Controllers\Pengepul;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Models\PermintaanJemput;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    use PunyaProfilPengepul;

    public function index(): View
    {
        $profil = $this->profil();

        $selesai = $profil->permintaan()->where('status', StatusPermintaan::Selesai);

        $ringkasan = [
            'saldo' => (float) $profil->saldo,
            'transaksi' => $selesai->clone()->count(),
            'berat' => (float) $selesai->clone()->sum('berat_final_kg'),
            'komisiBulanIni' => (float) $profil->permintaan()
                ->where('status', StatusPermintaan::Selesai)
                ->whereMonth('selesai_pada', now()->month)
                ->whereYear('selesai_pada', now()->year)
                ->sum('jumlah_komisi'),
            'omzetBulanIni' => (float) $profil->permintaan()
                ->where('status', StatusPermintaan::Selesai)
                ->whereMonth('selesai_pada', now()->month)
                ->whereYear('selesai_pada', now()->year)
                ->sum('total_final'),
        ];

        $masuk = $profil->permintaan()
            ->where('status', StatusPermintaan::Diajukan)
            ->with(['warga.wilayah', 'item.kategori'])
            ->latest()
            ->take(5)
            ->get();

        $berjalan = $profil->permintaan()
            ->whereIn('status', [
                StatusPermintaan::Dijadwalkan->value,
                StatusPermintaan::Dijemput->value,
                StatusPermintaan::MenungguKonfirmasi->value,
            ])
            ->with(['warga', 'item.kategori'])
            ->orderBy('jadwal_tanggal')
            ->get();

        // Permintaan terbuka yang belum diklaim, dalam radius layanan.
        $terbuka = PermintaanJemput::where('permintaan_terbuka', true)
            ->whereNull('profil_pengepul_id')
            ->where('status', StatusPermintaan::Diajukan)
            ->with(['warga.wilayah', 'item.kategori'])
            ->latest()
            ->take(3)
            ->get();

        $omzetHarian = $profil->permintaan()
            ->where('status', StatusPermintaan::Selesai)
            ->where('selesai_pada', '>=', now()->subDays(14))
            ->selectRaw('DATE(selesai_pada) as tanggal, SUM(total_final) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        return view('pengepul.dashboard', compact(
            'profil', 'ringkasan', 'masuk', 'berjalan', 'terbuka', 'omzetHarian'
        ));
    }

    public function performa(): View
    {
        $profil = $this->profil();

        // Ulasan yang belum dibalas ditampilkan lebih dulu.
        $ulasan = $profil->ulasan()->with('warga')
            ->orderByRaw('CASE WHEN balasan IS NULL THEN 0 ELSE 1 END')
            ->latest()
            ->take(15)
            ->get();

        $sebaranRating = $profil->ulasan()
            ->selectRaw('rating, COUNT(*) as jumlah')
            ->groupBy('rating')
            ->pluck('jumlah', 'rating');

        return view('pengepul.performa', compact('profil', 'ulasan', 'sebaranRating'));
    }
}
