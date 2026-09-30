<?php

namespace App\Services;

use App\Enums\JenisMutasi;
use App\Enums\StatusTopup;
use App\Exceptions\AksiTidakValid;
use App\Models\PermintaanTopup;
use App\Models\ProfilPengepul;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TopupService
{
    public function __construct(
        private WalletService $dompet,
        private AuditService $audit,
    ) {}

    public function ajukan(ProfilPengepul $pengepul, float $jumlah, string $bank, string $namaPengirim, UploadedFile $bukti): PermintaanTopup
    {
        $minimum = (float) pengaturan('topup_minimum', 25000);

        if ($jumlah < $minimum) {
            throw new AksiTidakValid('Nominal isi saldo minimal '.rupiah($minimum).'.');
        }

        if ($pengepul->topup()->where('status', StatusTopup::Menunggu)->exists()) {
            throw new AksiTidakValid('Masih ada pengajuan isi saldo yang menunggu verifikasi. Tunggu hingga selesai diperiksa admin.');
        }

        return PermintaanTopup::create([
            'kode' => $this->kodeBaru(),
            'profil_pengepul_id' => $pengepul->id,
            'jumlah' => round($jumlah, 2),
            'bank_pengirim' => $bank,
            'nama_pengirim' => $namaPengirim,
            // Bukti transfer memuat data rekening, jadi tidak disimpan di disk publik.
            'bukti_transfer' => $bukti->store('bukti-transfer', 'local'),
            'status' => StatusTopup::Menunggu,
        ]);
    }

    public function setujui(PermintaanTopup $topup, User $admin): void
    {
        DB::transaction(function () use ($topup, $admin) {
            $terkunci = PermintaanTopup::whereKey($topup->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusTopup::Menunggu) {
                throw new AksiTidakValid('Pengajuan ini sudah diperiksa sebelumnya.');
            }

            $terkunci->update([
                'status' => StatusTopup::Disetujui,
                'diverifikasi_oleh' => $admin->id,
                'diverifikasi_pada' => now(),
            ]);

            $this->dompet->kredit(
                $terkunci->pengepul,
                (float) $terkunci->jumlah,
                JenisMutasi::Topup,
                $terkunci,
                "Isi saldo {$terkunci->kode} via {$terkunci->bank_pengirim}"
            );
        });

        $this->audit->catat('topup.disetujui', $topup, [], ['jumlah' => (float) $topup->jumlah]);
    }

    public function tolak(PermintaanTopup $topup, User $admin, string $catatan): void
    {
        if ($topup->status !== StatusTopup::Menunggu) {
            throw new AksiTidakValid('Pengajuan ini sudah diperiksa sebelumnya.');
        }

        $topup->update([
            'status' => StatusTopup::Ditolak,
            'catatan_admin' => $catatan,
            'diverifikasi_oleh' => $admin->id,
            'diverifikasi_pada' => now(),
        ]);

        $this->audit->catat('topup.ditolak', $topup, [], ['catatan' => $catatan]);
    }

    private function kodeBaru(): string
    {
        $n = (int) PermintaanTopup::max('id');

        do {
            $kode = 'TOP-'.str_pad((string) ++$n, 6, '0', STR_PAD_LEFT);
        } while (PermintaanTopup::where('kode', $kode)->exists());

        return $kode;
    }
}
