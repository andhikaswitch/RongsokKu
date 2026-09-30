<?php

namespace App\Http\Controllers\Pengepul;

use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Http\Requests\Pengepul\VerifikasiRequest;
use App\Services\VerifikasiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VerifikasiController extends Controller
{
    use PunyaProfilPengepul;

    public function form(): View|RedirectResponse
    {
        $profil = $this->profil();

        if (in_array($profil->status_verifikasi, [StatusVerifikasi::Menunggu, StatusVerifikasi::Terverifikasi], true)) {
            return redirect()->route('pengepul.verifikasi.status');
        }

        return view('pengepul.verifikasi.form', compact('profil'));
    }

    public function ajukan(VerifikasiRequest $request, VerifikasiService $service): RedirectResponse
    {
        $service->ajukan(
            $this->profil(),
            $request->validated(),
            $request->file('foto_ktp'),
            $request->file('foto_lapak'),
        );

        return redirect()
            ->route('pengepul.verifikasi.status')
            ->with('sukses', 'Berkas terkirim. Admin akan memeriksanya dalam 1–2 hari kerja.');
    }

    public function status(): View
    {
        return view('pengepul.verifikasi.status', ['profil' => $this->profil()]);
    }
}
