<?php

namespace App\Services;

use App\Models\HargaPengepul;
use App\Models\KategoriSampah;
use App\Models\ProfilPengepul;
use Illuminate\Support\Facades\DB;

class HargaPengepulService
{
    /**
     * Simpan seluruh daftar harga sekaligus.
     *
     * Harga yang menyimpang dari indeks sengaja TIDAK ditolak: pengepul bebas
     * menentukan harganya, dan warga yang menilai lewat label peringatan.
     *
     * @param  array<int, array{harga?: ?string, min_berat?: ?string, menerima?: mixed}>  $baris  dikunci oleh id kategori
     * @return int jumlah kategori yang dipasang
     */
    public function simpan(ProfilPengepul $pengepul, array $baris): int
    {
        $kategori = KategoriSampah::turunan()->aktif()->get()->keyBy('id');
        $dipasang = 0;

        DB::transaction(function () use ($pengepul, $baris, $kategori, &$dipasang) {
            foreach ($kategori as $id => $k) {
                $data = $baris[$id] ?? [];
                $harga = isset($data['harga']) && $data['harga'] !== '' ? (float) $data['harga'] : null;

                // Kosongkan harga = tidak membeli kategori ini. Limbah B3 juga
                // otomatis dilepas bila pengepul tidak memiliki izin.
                if ($harga === null || $harga <= 0 || ($k->limbah_b3 && ! $pengepul->izin_b3)) {
                    HargaPengepul::where('profil_pengepul_id', $pengepul->id)
                        ->where('kategori_sampah_id', $id)
                        ->delete();

                    continue;
                }

                HargaPengepul::updateOrCreate(
                    ['profil_pengepul_id' => $pengepul->id, 'kategori_sampah_id' => $id],
                    [
                        'harga_per_satuan' => round($harga, 2),
                        'min_berat' => max(0, (float) ($data['min_berat'] ?? 0)),
                        'sedang_menerima' => ! empty($data['menerima']),
                    ]
                );

                $dipasang++;
            }
        });

        return $dipasang;
    }
}
