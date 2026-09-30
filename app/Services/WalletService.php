<?php

namespace App\Services;

use App\Enums\JenisMutasi;
use App\Models\MutasiSaldo;
use App\Models\ProfilPengepul;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu untuk mengubah saldo pengepul.
 *
 * Buku besar mutasi_saldo adalah sumber kebenaran; kolom saldo di profil
 * hanya cache. Karena itu setiap perubahan dicatat bersama saldo sebelum
 * dan sesudahnya, dan baris profil dikunci agar dua transaksi yang terjadi
 * bersamaan tidak saling menimpa.
 */
class WalletService
{
    public function kredit(
        ProfilPengepul $pengepul,
        float $jumlah,
        JenisMutasi $jenis,
        ?Model $referensi,
        string $keterangan,
    ): MutasiSaldo {
        return $this->mutasi($pengepul, abs($jumlah), $jenis, $referensi, $keterangan);
    }

    /**
     * Saldo boleh menjadi minus: komisi transaksi besar bisa melebihi saldo
     * yang tersisa. Minus diperlakukan sebagai utang, dan pengepul otomatis
     * tertahan menerima permintaan baru sampai saldonya diisi kembali.
     */
    public function debit(
        ProfilPengepul $pengepul,
        float $jumlah,
        JenisMutasi $jenis,
        ?Model $referensi,
        string $keterangan,
    ): MutasiSaldo {
        return $this->mutasi($pengepul, -abs($jumlah), $jenis, $referensi, $keterangan);
    }

    private function mutasi(
        ProfilPengepul $pengepul,
        float $jumlah,
        JenisMutasi $jenis,
        ?Model $referensi,
        string $keterangan,
    ): MutasiSaldo {
        return DB::transaction(function () use ($pengepul, $jumlah, $jenis, $referensi, $keterangan) {
            $terkunci = ProfilPengepul::whereKey($pengepul->getKey())->lockForUpdate()->firstOrFail();

            $sebelum = (float) $terkunci->saldo;
            $sesudah = round($sebelum + $jumlah, 2);

            $mutasi = MutasiSaldo::create([
                'profil_pengepul_id' => $terkunci->id,
                'jenis' => $jenis,
                'jumlah' => round($jumlah, 2),
                'saldo_sebelum' => $sebelum,
                'saldo_sesudah' => $sesudah,
                'referensi_type' => $referensi ? $referensi::class : null,
                'referensi_id' => $referensi?->getKey(),
                'keterangan' => $keterangan,
                'dicatat_oleh' => auth()->id(),
            ]);

            $terkunci->forceFill(['saldo' => $sesudah])->save();
            $pengepul->setAttribute('saldo', $sesudah);

            return $mutasi;
        });
    }

    /** Hitung ulang saldo dari buku besar, untuk memeriksa konsistensi cache. */
    public function saldoMenurutBukuBesar(ProfilPengepul $pengepul): float
    {
        $terakhir = $pengepul->mutasi()->latest('id')->first();

        return $terakhir ? (float) $terakhir->saldo_sesudah : 0.0;
    }
}
