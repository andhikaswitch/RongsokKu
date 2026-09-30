<?php

namespace App\Http\Controllers\Publik;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Models\IndeksHarga;
use App\Models\KategoriSampah;
use App\Models\PermintaanJemput;
use App\Models\ProfilPengepul;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $golongan = KategoriSampah::utama()->aktif()
            ->with(['anak' => fn ($q) => $q->aktif()])
            ->orderBy('urutan')
            ->get();

        $selesai = PermintaanJemput::where('status', StatusPermintaan::Selesai);

        $statistik = [
            'berat' => (float) $selesai->clone()->sum('berat_final_kg'),
            'transaksi' => $selesai->clone()->count(),
            'nilai' => (float) $selesai->clone()->sum('total_final'),
            'pengepul' => ProfilPengepul::sudahTerverifikasi()->count(),
        ];

        // Estimasi CO2 yang dicegah, dihitung dari faktor tiap kategori.
        $statistik['co2'] = (float) \App\Models\ItemPermintaan::query()
            ->join('kategori_sampah', 'kategori_sampah.id', '=', 'item_permintaan.kategori_sampah_id')
            ->join('permintaan_jemput', 'permintaan_jemput.id', '=', 'item_permintaan.permintaan_jemput_id')
            ->where('permintaan_jemput.status', StatusPermintaan::Selesai->value)
            ->sum(\DB::raw('item_permintaan.berat_final * kategori_sampah.faktor_co2_per_kg'));

        $indeksSorot = IndeksHarga::with('kategori')
            ->whereNull('wilayah_id')
            ->whereDate('tanggal', now()->toDateString())
            ->where('jumlah_transaksi', '>=', IndeksHarga::MIN_TRANSAKSI)
            ->orderByDesc('jumlah_transaksi')
            ->take(4)
            ->get();

        $pengepulUnggulan = ProfilPengepul::sudahTerverifikasi()
            ->with('user.wilayah.induk')
            ->orderByDesc('rating_rata')
            ->orderByDesc('total_transaksi')
            ->take(3)
            ->get();

        return view('publik.beranda', compact('golongan', 'statistik', 'indeksSorot', 'pengepulUnggulan'));
    }

    /** Ganti tema terang/gelap tanpa JavaScript: simpan pilihan di session. */
    public function gantiTema(Request $request): RedirectResponse
    {
        session(['tema' => session('tema') === 'gelap' ? 'terang' : 'gelap']);

        return redirect()->to($request->input('kembali', url()->previous()));
    }
}
