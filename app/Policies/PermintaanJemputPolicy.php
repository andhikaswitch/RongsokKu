<?php

namespace App\Policies;

use App\Models\PermintaanJemput;
use App\Models\User;

class PermintaanJemputPolicy
{
    /**
     * Warga pemilik, pengepul yang ditunjuk, dan admin boleh melihat.
     * Permintaan terbuka yang belum diklaim juga boleh dilihat pengepul
     * terverifikasi, agar mereka bisa menilai sebelum mengklaim.
     */
    public function view(User $user, PermintaanJemput $permintaan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isWarga()) {
            return (int) $permintaan->warga_id === (int) $user->id;
        }

        $profil = $user->profilPengepul;

        if (! $profil) {
            return false;
        }

        if ($permintaan->profil_pengepul_id === null) {
            return $permintaan->permintaan_terbuka && $profil->terverifikasi();
        }

        return (int) $permintaan->profil_pengepul_id === (int) $profil->id;
    }
}
