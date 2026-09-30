<?php

namespace App\Http\Controllers\Traits;

use App\Models\ProfilPengepul;

trait PunyaProfilPengepul
{
    protected function profil(): ProfilPengepul
    {
        $profil = auth()->user()->profilPengepul;

        abort_if(! $profil, 403, 'Profil pengepul belum dibuat.');

        return $profil;
    }
}
