<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPermintaan;
use App\Enums\StatusTopup;
use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Models\PermintaanJemput;
use App\Models\PermintaanTopup;
use App\Models\ProfilPengepul;
use App\Models\Sengketa;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $selesai = PermintaanJemput::where('status', StatusPermintaan::Selesai);

        $kpi = [
            'pendapatan' => (float) $selesai->clone()->sum('jumlah_komisi'),
            'gmv' => (float) $selesai->clone()->sum('total_final'),
            'transaksi' => $selesai->clone()->count(),
            'berat' => (float) $selesai->clone()->sum('berat_final_kg'),
            'warga' => User::where('peran', 'warga')->count(),
            'pengepul' => ProfilPengepul::sudahTerverifikasi()->count(),
        ];

        $kpi['pendapatanBulanIni'] = (float) PermintaanJemput::where('status', StatusPermintaan::Selesai)
            ->whereMonth('selesai_pada', now()->month)
            ->whereYear('selesai_pada', now()->year)
            ->sum('jumlah_komisi');

        $antrean = [
            'verifikasi' => ProfilPengepul::where('status_verifikasi', StatusVerifikasi::Menunggu)->count(),
            'topup' => PermintaanTopup::where('status', StatusTopup::Menunggu)->count(),
            'sengketa' => Sengketa::where('status', 'menunggu')->count(),
        ];

        $komisiHarian = PermintaanJemput::where('status', StatusPermintaan::Selesai)
            ->where('selesai_pada', '>=', now()->subDays(30))
            ->selectRaw('DATE(selesai_pada) as tanggal, SUM(jumlah_komisi) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        $statusTransaksi = PermintaanJemput::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $pengepulTeratas = ProfilPengepul::sudahTerverifikasi()
            ->orderByDesc('total_transaksi')
            ->take(5)
            ->get();

        // Pengepul dengan kepatuhan harga rendah perlu diawasi admin.
        $perluDiawasi = ProfilPengepul::sudahTerverifikasi()
            ->where('skor_kepatuhan_harga', '<', 85)
            ->where('total_transaksi', '>=', 3)
            ->orderBy('skor_kepatuhan_harga')
            ->take(5)
            ->get();

        $transaksiTerbaru = PermintaanJemput::with(['warga', 'pengepul'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'kpi', 'antrean', 'komisiHarian', 'statusTransaksi',
            'pengepulTeratas', 'perluDiawasi', 'transaksiTerbaru'
        ));
    }
}
