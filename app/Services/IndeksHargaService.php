<?php

namespace App\Services;

use App\Enums\StatusPermintaan;
use App\Models\IndeksHarga;
use App\Models\ItemPermintaan;
use App\Models\KategoriSampah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menghitung Indeks Harga RongsokKu dari transaksi yang benar-benar selesai.
 *
 * Inilah jawaban atas kebutuhan "harga pasar real-time": alih-alih menunggu
 * lembaga resmi yang belum ada, harga pasar disusun dari harga tempat
 * transaksi sungguhan terjadi, seperti cara kerja sebuah bursa.
 */
class IndeksHargaService
{
    /** Hitung indeks seluruh kategori untuk satu tanggal. */
    public function hitungSemua(?Carbon $tanggal = null, int $jendelaHari = 30): int
    {
        $tanggal ??= now();
        $jumlah = 0;

        foreach (KategoriSampah::turunan()->aktif()->get() as $kategori) {
            if ($this->hitungKategori($kategori, $tanggal, $jendelaHari)) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    public function hitungKategori(KategoriSampah $kategori, Carbon $tanggal, int $jendelaHari = 30): ?IndeksHarga
    {
        $harga = $this->hargaTransaksi($kategori, $tanggal, $jendelaHari);

        if ($harga->isEmpty()) {
            return null;
        }

        $sebelumnya = IndeksHarga::where('kategori_sampah_id', $kategori->id)
            ->whereNull('wilayah_id')
            ->where('tanggal', '<', $tanggal->toDateString())
            ->latest('tanggal')
            ->first();

        $median = $this->median($harga->pluck('harga')->all());

        $perubahan = $sebelumnya && (float) $sebelumnya->harga_median > 0
            ? round((($median - (float) $sebelumnya->harga_median) / (float) $sebelumnya->harga_median) * 100, 2)
            : null;

        return IndeksHarga::updateOrCreate(
            [
                'kategori_sampah_id' => $kategori->id,
                'wilayah_id' => null,
                'tanggal' => $tanggal->toDateString(),
            ],
            [
                'harga_median' => $median,
                'harga_min' => $harga->min('harga'),
                'harga_max' => $harga->max('harga'),
                'harga_rata' => round($harga->avg('harga'), 2),
                'jumlah_transaksi' => $harga->count(),
                'total_berat_kg' => round($harga->sum('berat'), 2),
                'perubahan_persen' => $perubahan,
            ]
        );
    }

    /**
     * Harga per satuan dari tiap item transaksi yang selesai dalam jendela waktu.
     *
     * @return Collection<int, array{harga: float, berat: float}>
     */
    private function hargaTransaksi(KategoriSampah $kategori, Carbon $tanggal, int $jendelaHari): Collection
    {
        return ItemPermintaan::query()
            ->where('kategori_sampah_id', $kategori->id)
            ->whereNotNull('harga_final_per_satuan')
            ->whereHas('permintaan', function ($q) use ($tanggal, $jendelaHari) {
                $q->where('status', StatusPermintaan::Selesai)
                    ->whereBetween('selesai_pada', [
                        $tanggal->copy()->subDays($jendelaHari)->startOfDay(),
                        $tanggal->copy()->endOfDay(),
                    ]);
            })
            ->get(['harga_final_per_satuan', 'berat_final'])
            ->map(fn ($item) => [
                'harga' => (float) $item->harga_final_per_satuan,
                'berat' => (float) $item->berat_final,
            ]);
    }

    /**
     * Median dipilih karena tahan terhadap pencilan: satu transaksi dengan
     * harga tidak wajar tidak akan menggeser angka yang dilihat publik.
     */
    public function median(array $angka): float
    {
        if ($angka === []) {
            return 0;
        }

        sort($angka);
        $n = count($angka);
        $tengah = intdiv($n, 2);

        return $n % 2
            ? (float) $angka[$tengah]
            : round(($angka[$tengah - 1] + $angka[$tengah]) / 2, 2);
    }

    /** Riwayat indeks untuk grafik sparkline. */
    public function riwayat(KategoriSampah $kategori, int $hari = 14): Collection
    {
        return IndeksHarga::where('kategori_sampah_id', $kategori->id)
            ->whereNull('wilayah_id')
            ->where('tanggal', '>=', now()->subDays($hari)->toDateString())
            ->orderBy('tanggal')
            ->get();
    }
}
