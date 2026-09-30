<?php

namespace App\Http\Controllers;

use App\Http\Requests\Umum\GantiSandiRequest;
use App\Http\Requests\Umum\ProfilRequest;
use App\Models\Wilayah;
use App\Services\PenggunaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Profil akun, dipakai bersama oleh warga dan pengepul. */
class ProfilController extends Controller
{
    public function __construct(private PenggunaService $pengguna) {}

    public function edit(Request $request): View
    {
        return view('profil.edit', [
            'pengguna' => $request->user()->load('wilayah.induk'),
            'kelurahan' => Wilayah::kelurahan()->with('induk')->orderBy('induk_id')->orderBy('nama')->get()
                ->groupBy(fn ($w) => $w->induk?->nama ?? 'Lainnya'),
        ]);
    }

    public function update(ProfilRequest $request): RedirectResponse
    {
        $this->pengguna->perbaruiProfil($request->user(), $request->validated(), $request->file('foto_profil'));

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function sandi(GantiSandiRequest $request): RedirectResponse
    {
        $this->pengguna->gantiSandi($request->user(), $request->validated('password'));

        return back()->with('sukses', 'Kata sandi berhasil diganti.');
    }
}
