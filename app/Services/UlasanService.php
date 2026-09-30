<?php

namespace App\Services;

use App\Exceptions\AksiTidakValid;
use App\Models\PermintaanJemput;
use App\Models\ProfilPengepul;
use App\Models\Ulasan;
use App\Models\User;

class UlasanService
{
    public function __construct(private MetrikPengepulService $metrik) {}

    public function kirim(PermintaanJemput $permintaan, User $warga, int $rating, ?string $komentar): Ulasan
    {
        if ((int) $permintaan->warga_id !== (int) $warga->id) {
            throw new AksiTidakValid('Permintaan ini bukan milik Anda.');
        }

        if (! $permintaan->bisaDiulas()) {
            throw new AksiTidakValid('Ulasan hanya bisa diberikan sekali untuk transaksi yang sudah selesai.');
        }

        $ulasan = Ulasan::create([
            'permintaan_jemput_id' => $permintaan->id,
            'warga_id' => $warga->id,
            'profil_pengepul_id' => $permintaan->profil_pengepul_id,
            'rating' => max(1, min(5, $rating)),
            'komentar' => $komentar,
        ]);

        // Rating dihitung ulang murni dari ulasan. Perilaku harga tidak
        // pernah ikut memengaruhi bintang (lihat MetrikPengepulService).
        $this->metrik->segarkan($permintaan->pengepul);

        return $ulasan;
    }

    public function balas(Ulasan $ulasan, ProfilPengepul $pengepul, string $balasan): void
    {
        if ((int) $ulasan->profil_pengepul_id !== (int) $pengepul->id) {
            throw new AksiTidakValid('Ulasan ini bukan untuk lapak Anda.');
        }

        $ulasan->update([
            'balasan' => $balasan,
            'dibalas_pada' => now(),
        ]);
    }
}
