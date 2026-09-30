<?php

namespace App\Http\Controllers\Pengepul;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Models\PermintaanJemput;
use App\Services\PermintaanJemputService;
use App\Support\Haversine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TerbukaController extends Controller
{
    use PunyaProfilPengepul;

    public function index(): View
    {
        $profil = $this->profil()->load(['harga', 'user']);

        $diterima = $profil->harga->where('sedang_menerima', true)->pluck('kategori_sampah_id')->all();

        $permintaan = PermintaanJemput::where('permintaan_terbuka', true)
            ->whereNull('profil_pengepul_id')
            ->where('status', StatusPermintaan::Diajukan)
            ->with(['warga.wilayah.induk', 'item.kategori'])
            ->latest()
            ->get()
            ->map(function (PermintaanJemput $p) use ($profil, $diterima) {
                $p->setAttribute('jarak_saya', $profil->user->latitude && $p->latitude
                    ? Haversine::jarak((float) $profil->user->latitude, (float) $profil->user->longitude, (float) $p->latitude, (float) $p->longitude)
                    : null);

                // Pengepul hanya bisa mengklaim bila menerima semua jenis barang di dalamnya.
                $p->setAttribute('bisa_diklaim', $p->item->every(fn ($i) => in_array($i->kategori_sampah_id, $diterima, true)
                    && (! $i->kategori->limbah_b3 || $profil->izin_b3)));

                return $p;
            })
            ->filter(fn ($p) => $p->jarak_saya === null || $p->jarak_saya <= $profil->radius_layanan_km)
            ->sortBy('jarak_saya')
            ->values();

        return view('pengepul.terbuka.index', compact('permintaan', 'profil'));
    }

    public function klaim(PermintaanJemput $permintaan, PermintaanJemputService $service): RedirectResponse
    {
        $service->klaimTerbuka($permintaan, $this->profil());

        return redirect()
            ->route('pengepul.permintaan.show', $permintaan)
            ->with('sukses', "Berhasil! Permintaan {$permintaan->kode} sekarang milik Anda dan sudah dijadwalkan.");
    }
}
