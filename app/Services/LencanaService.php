<?php

namespace App\Services;

use App\Models\Lencana;
use App\Models\User;
use Illuminate\Support\Collection;

class LencanaService
{
    /**
     * Berikan semua lencana yang ambang beratnya sudah terlampaui.
     *
     * @return Collection<int, Lencana> lencana yang baru saja diraih
     */
    public function periksa(User $warga): Collection
    {
        $berat = $warga->totalBeratDidaurUlang();
        $dimiliki = $warga->lencana()->pluck('lencana.id')->all();

        $baru = Lencana::where('syarat_berat_kg', '<=', $berat)
            ->whereNotIn('id', $dimiliki)
            ->orderBy('syarat_berat_kg')
            ->get();

        foreach ($baru as $lencana) {
            $warga->lencana()->attach($lencana->id, ['diraih_pada' => now()]);
        }

        return $baru;
    }
}
