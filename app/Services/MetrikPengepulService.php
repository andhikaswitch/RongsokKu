<?php

namespace App\Services;

use App\Enums\StatusPermintaan;
use App\Models\ProfilPengepul;

/**
 * Menghitung metrik objektif pengepul langsung dari data transaksi.
 *
 * Metrik ini sengaja dipisahkan dari rating bintang: rating menilai mutu
 * layanan menurut warga, sedangkan metrik di sini mengukur perilaku yang
 * bisa dibuktikan angkanya, terutama kepatuhan terhadap harga yang dipajang.
 */
class MetrikPengepulService
{
    public function segarkan(ProfilPengepul $pengepul): void
    {
        $pengepul->update([
            'skor_kepatuhan_harga' => $this->kepatuhanHarga($pengepul),
            'tingkat_penerimaan' => $this->tingkatPenerimaan($pengepul),
            'ketepatan_waktu' => $this->ketepatanWaktu($pengepul),
            'pembatalan_sepihak' => $this->pembatalanSepihak($pengepul),
            'rating_rata' => round((float) $pengepul->ulasan()->avg('rating'), 2),
            'jumlah_ulasan' => $pengepul->ulasan()->count(),
            'total_transaksi' => $pengepul->permintaan()->where('status', StatusPermintaan::Selesai)->count(),
            'total_berat_kg' => round((float) $pengepul->permintaan()
                ->where('status', StatusPermintaan::Selesai)->sum('berat_final_kg'), 2),
        ]);
    }

    /**
     * Persentase transaksi yang dibayar sesuai atau di atas harga yang
     * dipajang saat warga mengajukan. Inilah pendeteksi "harga umpan".
     */
    public function kepatuhanHarga(ProfilPengepul $pengepul): float
    {
        $transaksi = $pengepul->permintaan()
            ->where('status', StatusPermintaan::Selesai)
            ->with('item')
            ->get();

        if ($transaksi->isEmpty()) {
            return 100;
        }

        $patuh = $transaksi->filter(function ($permintaan) {
            foreach ($permintaan->item as $item) {
                if ($item->harga_final_per_satuan === null) {
                    continue;
                }

                // Toleransi 1% untuk pembulatan harga di lapangan.
                if ((float) $item->harga_final_per_satuan < (float) $item->harga_estimasi_per_satuan * 0.99) {
                    return false;
                }
            }

            return true;
        })->count();

        return round(($patuh / $transaksi->count()) * 100, 2);
    }

    /** Persentase permintaan masuk yang diterima, bukan ditolak. */
    public function tingkatPenerimaan(ProfilPengepul $pengepul): float
    {
        $total = $pengepul->permintaan()
            ->whereIn('status', [
                StatusPermintaan::Selesai->value,
                StatusPermintaan::Dijadwalkan->value,
                StatusPermintaan::Dijemput->value,
                StatusPermintaan::MenungguKonfirmasi->value,
                StatusPermintaan::Ditolak->value,
                StatusPermintaan::Sengketa->value,
            ])
            ->count();

        if ($total === 0) {
            return 100;
        }

        $ditolak = $pengepul->permintaan()->where('status', StatusPermintaan::Ditolak)->count();

        return round((($total - $ditolak) / $total) * 100, 2);
    }

    /** Persentase penjemputan yang terjadi pada tanggal yang dijadwalkan. */
    public function ketepatanWaktu(ProfilPengepul $pengepul): float
    {
        $selesai = $pengepul->permintaan()
            ->where('status', StatusPermintaan::Selesai)
            ->whereNotNull('jadwal_tanggal')
            ->whereNotNull('dijemput_pada')
            ->get(['jadwal_tanggal', 'dijemput_pada']);

        if ($selesai->isEmpty()) {
            return 100;
        }

        $tepat = $selesai->filter(
            fn ($p) => $p->dijemput_pada->toDateString() <= $p->jadwal_tanggal->toDateString()
        )->count();

        return round(($tepat / $selesai->count()) * 100, 2);
    }

    public function pembatalanSepihak(ProfilPengepul $pengepul): int
    {
        return $pengepul->permintaan()
            ->where('status', StatusPermintaan::Dibatalkan)
            ->where('dibatalkan_oleh', 'pengepul')
            ->count();
    }
}
