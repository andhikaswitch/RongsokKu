<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Models\ItemPermintaan;
use App\Models\Lencana;
use App\Models\PermintaanJemput;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $warga = auth()->user();

        $selesai = PermintaanJemput::where('warga_id', $warga->id)
            ->where('status', StatusPermintaan::Selesai);

        $ringkasan = [
            'berat' => (float) $selesai->clone()->sum('berat_final_kg'),
            'pendapatan' => (float) $selesai->clone()->sum('total_final'),
            'transaksi' => $selesai->clone()->count(),
            'co2' => $this->totalCo2($warga->id),
        ];

        $berjalan = PermintaanJemput::where('warga_id', $warga->id)
            ->berjalan()
            ->with(['pengepul.user', 'item.kategori'])
            ->latest()
            ->get();

        $riwayat = PermintaanJemput::where('warga_id', $warga->id)
            ->where('status', StatusPermintaan::Selesai)
            ->with(['pengepul', 'ulasan'])
            ->latest('selesai_pada')
            ->take(5)
            ->get();

        $lencanaSaya = $warga->lencana()->orderBy('urutan')->get();
        $lencanaBerikut = Lencana::where('syarat_berat_kg', '>', $ringkasan['berat'])
            ->orderBy('syarat_berat_kg')
            ->first();

        return view('warga.dashboard', compact(
            'ringkasan', 'berjalan', 'riwayat', 'lencanaSaya', 'lencanaBerikut'
        ));
    }

    public function dampak(): View
    {
        $warga = auth()->user();

        $berat = (float) PermintaanJemput::where('warga_id', $warga->id)
            ->where('status', StatusPermintaan::Selesai)
            ->sum('berat_final_kg');

        $perKategori = ItemPermintaan::query()
            ->join('kategori_sampah', 'kategori_sampah.id', '=', 'item_permintaan.kategori_sampah_id')
            ->join('permintaan_jemput', 'permintaan_jemput.id', '=', 'item_permintaan.permintaan_jemput_id')
            ->where('permintaan_jemput.warga_id', $warga->id)
            ->where('permintaan_jemput.status', StatusPermintaan::Selesai->value)
            ->groupBy('kategori_sampah.id', 'kategori_sampah.nama', 'kategori_sampah.ikon')
            ->select('kategori_sampah.nama', 'kategori_sampah.ikon')
            ->selectRaw('SUM(item_permintaan.berat_final) as berat')
            ->selectRaw('SUM(item_permintaan.subtotal_final) as nilai')
            ->orderByDesc('berat')
            ->get();

        $semuaLencana = Lencana::orderBy('urutan')->get();
        $lencanaSaya = $warga->lencana()->pluck('lencana.id')->all();

        return view('warga.dampak', [
            'berat' => $berat,
            'co2' => $this->totalCo2($warga->id),
            'perKategori' => $perKategori,
            'semuaLencana' => $semuaLencana,
            'lencanaSaya' => $lencanaSaya,
        ]);
    }

    private function totalCo2(int $wargaId): float
    {
        return (float) ItemPermintaan::query()
            ->join('kategori_sampah', 'kategori_sampah.id', '=', 'item_permintaan.kategori_sampah_id')
            ->join('permintaan_jemput', 'permintaan_jemput.id', '=', 'item_permintaan.permintaan_jemput_id')
            ->where('permintaan_jemput.warga_id', $wargaId)
            ->where('permintaan_jemput.status', StatusPermintaan::Selesai->value)
            ->sum(DB::raw('item_permintaan.berat_final * kategori_sampah.faktor_co2_per_kg'));
    }
}
