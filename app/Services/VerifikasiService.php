<?php

namespace App\Services;

use App\Enums\StatusVerifikasi;
use App\Exceptions\AksiTidakValid;
use App\Models\ProfilPengepul;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VerifikasiService
{
    public function __construct(private AuditService $audit) {}

    public function ajukan(ProfilPengepul $profil, array $data, ?UploadedFile $ktp, ?UploadedFile $lapak): void
    {
        if ($profil->status_verifikasi === StatusVerifikasi::Terverifikasi) {
            throw new AksiTidakValid('Lapak Anda sudah terverifikasi.');
        }

        if (! $ktp && ! $profil->foto_ktp) {
            throw new AksiTidakValid('Foto KTP wajib diunggah.');
        }

        if (! $lapak && ! $profil->foto_lapak) {
            throw new AksiTidakValid('Foto lapak wajib diunggah.');
        }

        if ($ktp) {
            // KTP adalah data pribadi: disk lokal, hanya bisa dibuka admin.
            $this->hapusLama($profil->foto_ktp, 'local');
            $profil->foto_ktp = $ktp->store('ktp', 'local');
        }

        if ($lapak) {
            $this->hapusLama($profil->foto_lapak, 'public');
            $profil->foto_lapak = $lapak->store('lapak', 'public');
        }

        $profil->fill([
            'nama_usaha' => $data['nama_usaha'],
            'deskripsi' => $data['deskripsi'] ?? $profil->deskripsi,
            'izin_b3' => (bool) ($data['izin_b3'] ?? false),
        ]);
        $profil->status_verifikasi = StatusVerifikasi::Menunggu;
        $profil->alasan_penolakan = null;
        $profil->save();
    }

    public function setujui(ProfilPengepul $profil, User $admin): void
    {
        $this->pastikanMenunggu($profil);

        $profil->forceFill([
            'status_verifikasi' => StatusVerifikasi::Terverifikasi,
            'diverifikasi_pada' => now(),
            'diverifikasi_oleh' => $admin->id,
            'alasan_penolakan' => null,
        ])->save();

        $this->audit->catat('verifikasi.disetujui', $profil);
    }

    public function tolak(ProfilPengepul $profil, User $admin, string $alasan): void
    {
        $this->pastikanMenunggu($profil);

        $profil->forceFill([
            'status_verifikasi' => StatusVerifikasi::Ditolak,
            'diverifikasi_oleh' => $admin->id,
            'alasan_penolakan' => $alasan,
        ])->save();

        $this->audit->catat('verifikasi.ditolak', $profil, [], ['alasan' => $alasan]);
    }

    private function pastikanMenunggu(ProfilPengepul $profil): void
    {
        if ($profil->status_verifikasi !== StatusVerifikasi::Menunggu) {
            throw new AksiTidakValid('Pengajuan ini tidak sedang menunggu verifikasi.');
        }
    }

    private function hapusLama(?string $path, string $disk): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }
}
