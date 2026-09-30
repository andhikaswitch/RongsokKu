<?php

namespace App\Services;

use App\Enums\StatusPermintaan;
use App\Exceptions\AksiTidakValid;
use App\Models\PermintaanJemput;
use App\Models\Sengketa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SengketaService
{
    public const KEPUTUSAN = [
        'harga_awal' => 'Selesaikan dengan harga yang disepakati di awal',
        'hasil_timbang' => 'Selesaikan sesuai hasil timbangan pengepul',
        'batalkan' => 'Batalkan transaksi',
    ];

    public function __construct(
        private PermintaanJemputService $permintaan,
        private PenggunaService $pengguna,
        private AuditService $audit,
    ) {}

    /** Warga menolak hasil timbangan dan meminta admin menengahi. */
    public function ajukan(PermintaanJemput $permintaan, User $warga, string $alasan, string $deskripsi, ?UploadedFile $bukti): Sengketa
    {
        return DB::transaction(function () use ($permintaan, $warga, $alasan, $deskripsi, $bukti) {
            $p = PermintaanJemput::whereKey($permintaan->id)->lockForUpdate()->firstOrFail();

            if ((int) $p->warga_id !== (int) $warga->id) {
                throw new AksiTidakValid('Permintaan ini bukan milik Anda.');
            }

            if ($p->status !== StatusPermintaan::MenungguKonfirmasi) {
                throw new AksiTidakValid('Sengketa hanya bisa diajukan saat hasil timbangan menunggu konfirmasi.');
            }

            $p->forceFill(['status' => StatusPermintaan::Sengketa])->save();

            return Sengketa::create([
                'kode' => $this->kodeBaru(),
                'permintaan_jemput_id' => $p->id,
                'dilaporkan_oleh' => $warga->id,
                'alasan' => $alasan,
                'deskripsi' => $deskripsi,
                'bukti' => $bukti?->store('bukti-sengketa', 'local'),
                'status' => 'menunggu',
            ]);
        });
    }

    public function putuskan(Sengketa $sengketa, User $admin, string $keputusan, string $resolusi, bool $sanksi): void
    {
        if ($sengketa->status !== 'menunggu') {
            throw new AksiTidakValid('Sengketa ini sudah diputuskan sebelumnya.');
        }

        if (! array_key_exists($keputusan, self::KEPUTUSAN)) {
            throw new AksiTidakValid('Keputusan tidak dikenal.');
        }

        DB::transaction(function () use ($sengketa, $admin, $keputusan, $resolusi) {
            $p = PermintaanJemput::whereKey($sengketa->permintaan_jemput_id)->lockForUpdate()->firstOrFail();

            if ($p->status !== StatusPermintaan::Sengketa) {
                throw new AksiTidakValid('Status transaksi sudah berubah, muat ulang halaman.');
            }

            if ($keputusan === 'harga_awal') {
                // Pengepul wajib membayar sesuai harga yang dipajang saat pengajuan.
                foreach ($p->item as $item) {
                    $item->update([
                        'harga_final_per_satuan' => $item->harga_estimasi_per_satuan,
                        'subtotal_final' => round((float) $item->berat_final * (float) $item->harga_estimasi_per_satuan, 2),
                    ]);
                }
                $p->load('item');
            }

            if ($keputusan === 'batalkan') {
                $p->forceFill([
                    'status' => StatusPermintaan::Dibatalkan,
                    'dibatalkan_oleh' => 'admin',
                    'alasan_pembatalan' => 'Dibatalkan melalui keputusan sengketa '.$sengketa->kode,
                    'dibatalkan_pada' => now(),
                ])->save();
            } else {
                $this->permintaan->selesaikan($p);
            }

            $sengketa->update([
                'status' => 'selesai',
                'resolusi' => self::KEPUTUSAN[$keputusan].'. '.$resolusi,
                'ditangani_oleh' => $admin->id,
                'ditangani_pada' => now(),
            ]);
        });

        $sengketa->load('permintaan.pengepul.user');

        if ($sanksi && $sengketa->permintaan->pengepul) {
            $this->pengguna->nonaktifkan(
                $sengketa->permintaan->pengepul->user,
                'Sanksi atas sengketa '.$sengketa->kode
            );
        }

        $this->audit->catat('sengketa.diputuskan', $sengketa, [], [
            'keputusan' => $keputusan,
            'sanksi' => $sanksi,
            'resolusi' => $resolusi,
        ]);
    }

    private function kodeBaru(): string
    {
        $n = (int) Sengketa::max('id');

        do {
            $kode = 'SKT-'.str_pad((string) ++$n, 5, '0', STR_PAD_LEFT);
        } while (Sengketa::where('kode', $kode)->exists());

        return $kode;
    }
}
