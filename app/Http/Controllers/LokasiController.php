<?php

namespace App\Http\Controllers;

use App\Http\Requests\Umum\LokasiRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Memilih titik lokasi di peta (atau lewat GPS), dipakai bersama oleh warga dan pengepul. */
class LokasiController extends Controller
{
    public function edit(Request $request): View
    {
        $pengguna = $request->user()->load('wilayah');

        // Titik awal peta: titik tersimpan, lalu pusat kelurahan, lalu pusat Kabupaten Karawang.
        $lat = (float) ($pengguna->latitude ?: $pengguna->wilayah?->latitude ?: -6.3227);
        $lng = (float) ($pengguna->longitude ?: $pengguna->wilayah?->longitude ?: 107.3376);

        return view('profil.lokasi', [
            'awalan' => $this->awalan($request),
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    public function update(LokasiRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'latitude' => round((float) $request->validated('latitude'), 7),
            'longitude' => round((float) $request->validated('longitude'), 7),
        ])->save();

        return redirect()->route($this->awalan($request))->with('sukses', 'Titik lokasi berhasil disimpan.');
    }

    private function awalan(Request $request): string
    {
        return $request->user()->isPengepul() ? 'pengepul.akun' : 'warga.profil';
    }
}
