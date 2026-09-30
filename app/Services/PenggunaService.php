<?php

namespace App\Services;

use App\Exceptions\AksiTidakValid;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenggunaService
{
    public function __construct(private AuditService $audit) {}

    public function perbaruiProfil(User $pengguna, array $data, ?UploadedFile $foto): void
    {
        $wilayah = Wilayah::findOrFail($data['wilayah_id']);

        if ($foto) {
            if ($pengguna->foto_profil) {
                Storage::disk('public')->delete($pengguna->foto_profil);
            }
            $pengguna->foto_profil = $foto->store('profil', 'public');
        }

        $gantiWilayah = (int) $pengguna->wilayah_id !== (int) $wilayah->id;

        $pengguna->fill([
            'name' => $data['name'],
            'telepon' => $data['telepon'],
            'wilayah_id' => $wilayah->id,
            'alamat_detail' => $data['alamat_detail'],
        ]);

        // Tanpa GPS peramban, titik lokasi mengikuti kelurahan yang dipilih.
        if ($gantiWilayah || ! $pengguna->latitude) {
            $pengguna->latitude = $wilayah->latitude;
            $pengguna->longitude = $wilayah->longitude;
        }

        $pengguna->save();
    }

    public function gantiSandi(User $pengguna, string $sandiBaru): void
    {
        $pengguna->update(['password' => Hash::make($sandiBaru)]);
    }

    public function nonaktifkan(User $pengguna, string $alasan): void
    {
        if ($pengguna->id === auth()->id()) {
            throw new AksiTidakValid('Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        $pengguna->forceFill([
            'aktif' => false,
            'disuspend_pada' => now(),
            'alasan_suspend' => $alasan,
        ])->save();

        $this->audit->catat('pengguna.dinonaktifkan', $pengguna, [], ['alasan' => $alasan]);
    }

    public function aktifkan(User $pengguna): void
    {
        $pengguna->forceFill([
            'aktif' => true,
            'disuspend_pada' => null,
            'alasan_suspend' => null,
        ])->save();

        $this->audit->catat('pengguna.diaktifkan', $pengguna);
    }

    /** @return string kata sandi sementara yang baru, untuk disampaikan admin ke pemilik akun */
    public function aturUlangSandi(User $pengguna): string
    {
        $sandi = 'rk-'.Str::lower(Str::random(8));
        $pengguna->update(['password' => Hash::make($sandi)]);

        $this->audit->catat('pengguna.sandi_diatur_ulang', $pengguna);

        return $sandi;
    }
}
