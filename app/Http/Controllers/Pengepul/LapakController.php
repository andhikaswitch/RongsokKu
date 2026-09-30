<?php

namespace App\Http\Controllers\Pengepul;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Http\Requests\Pengepul\ProfilLapakRequest;
use App\Models\Ulasan;
use App\Services\UlasanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LapakController extends Controller
{
    use PunyaProfilPengepul;

    public function edit(): View
    {
        return view('pengepul.lapak', ['profil' => $this->profil()->load('user.wilayah')]);
    }

    public function update(ProfilLapakRequest $request): RedirectResponse
    {
        $profil = $this->profil();
        $data = $request->validated();

        if ($foto = $request->file('foto_lapak')) {
            if ($profil->foto_lapak) {
                Storage::disk('public')->delete($profil->foto_lapak);
            }
            $profil->foto_lapak = $foto->store('lapak', 'public');
        }

        $profil->fill([
            'nama_usaha' => $data['nama_usaha'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'jam_buka' => $data['jam_buka'].':00',
            'jam_tutup' => $data['jam_tutup'].':00',
            'radius_layanan_km' => $data['radius_layanan_km'],
            'sedang_menerima' => $request->boolean('sedang_menerima'),
        ])->save();

        return back()->with('sukses', 'Profil lapak diperbarui.');
    }

    public function balasUlasan(Request $request, Ulasan $ulasan, UlasanService $service): RedirectResponse
    {
        $data = $request->validate(['balasan' => ['required', 'string', 'max:1000']]);

        $service->balas($ulasan, $this->profil(), $data['balasan']);

        return back()->with('sukses', 'Balasan terkirim dan tampil di profil publik Anda.');
    }
}
