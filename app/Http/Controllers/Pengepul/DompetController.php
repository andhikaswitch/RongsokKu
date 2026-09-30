<?php

namespace App\Http\Controllers\Pengepul;

use App\Enums\JenisMutasi;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Http\Requests\Pengepul\TopupRequest;
use App\Services\TopupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DompetController extends Controller
{
    use PunyaProfilPengepul;

    public function index(Request $request): View
    {
        $profil = $this->profil();
        $jenis = JenisMutasi::tryFrom((string) $request->query('jenis'));

        $mutasi = $profil->mutasi()
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $topup = $profil->topup()->latest()->take(5)->get();

        $ringkasan = [
            'masuk' => (float) $profil->mutasi()->where('jumlah', '>', 0)->sum('jumlah'),
            'komisi' => abs((float) $profil->mutasi()->where('jenis', JenisMutasi::Komisi)->sum('jumlah')),
        ];

        return view('pengepul.dompet.index', compact('profil', 'mutasi', 'jenis', 'topup', 'ringkasan'));
    }

    public function formTopup(): View
    {
        return view('pengepul.dompet.topup', ['profil' => $this->profil()]);
    }

    public function topup(TopupRequest $request, TopupService $service): RedirectResponse
    {
        $topup = $service->ajukan(
            $this->profil(),
            (float) $request->validated('jumlah'),
            $request->validated('bank_pengirim'),
            $request->validated('nama_pengirim'),
            $request->file('bukti_transfer'),
        );

        return redirect()
            ->route('pengepul.dompet.index')
            ->with('sukses', "Pengajuan {$topup->kode} terkirim. Saldo bertambah setelah admin memverifikasi bukti transfer.");
    }
}
