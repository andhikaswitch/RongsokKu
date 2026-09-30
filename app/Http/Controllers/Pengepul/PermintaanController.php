<?php

namespace App\Http\Controllers\Pengepul;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Http\Requests\Pengepul\TerimaPermintaanRequest;
use App\Http\Requests\Pengepul\TimbangRequest;
use App\Models\PermintaanJemput;
use App\Services\PermintaanJemputService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermintaanController extends Controller
{
    use PunyaProfilPengepul;

    public function __construct(private PermintaanJemputService $service) {}

    public function index(Request $request): View
    {
        $profil = $this->profil();
        $status = StatusPermintaan::tryFrom((string) $request->query('status'));

        $permintaan = $profil->permintaan()
            ->with(['warga.wilayah', 'item.kategori'])
            ->when($status, fn ($q) => $q->where('status', $status))
            // Yang butuh tindakan pengepul tampil paling atas.
            ->orderByRaw("CASE status WHEN 'diajukan' THEN 0 WHEN 'dijemput' THEN 1 WHEN 'dijadwalkan' THEN 2 WHEN 'menunggu_konfirmasi' THEN 3 WHEN 'sengketa' THEN 4 ELSE 5 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $jumlahPerStatus = $profil->permintaan()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('pengepul.permintaan.index', compact('permintaan', 'status', 'jumlahPerStatus', 'profil'));
    }

    public function show(PermintaanJemput $permintaan): View
    {
        $this->authorize('view', $permintaan);

        $permintaan->load(['warga.wilayah.induk', 'item.kategori', 'ulasan', 'sengketa', 'wilayah.induk']);

        return view('pengepul.permintaan.show', [
            'permintaan' => $permintaan,
            'profil' => $this->profil(),
        ]);
    }

    public function terima(TerimaPermintaanRequest $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $this->service->terima($permintaan, $this->profil(), array_filter($request->validated()));

        return back()->with('sukses', 'Permintaan diterima dan dijadwalkan. Hubungi warga bila perlu.');
    }

    public function tolak(Request $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:255']]);

        $this->service->tolak($permintaan, $this->profil(), $data['alasan']);

        return redirect()->route('pengepul.permintaan.index')->with('info', "Permintaan {$permintaan->kode} ditolak.");
    }

    public function berangkat(PermintaanJemput $permintaan): RedirectResponse
    {
        $this->service->berangkat($permintaan, $this->profil());

        return back()->with('sukses', 'Status diperbarui: sedang menuju lokasi warga.');
    }

    public function timbang(TimbangRequest $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $this->service->timbang(
            $permintaan,
            $this->profil(),
            $request->validated('hasil'),
            $request->validated('catatan_pengepul'),
        );

        return back()->with('sukses', 'Hasil timbangan tersimpan. Bayar tunai ke warga, lalu minta warga mengonfirmasi di aplikasinya.');
    }

    public function batal(Request $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:255']]);

        $this->service->batalOlehPengepul($permintaan, $this->profil(), $data['alasan']);

        return back()->with('peringatan', 'Permintaan dibatalkan. Pembatalan sepihak tercatat di metrik performa Anda.');
    }
}
