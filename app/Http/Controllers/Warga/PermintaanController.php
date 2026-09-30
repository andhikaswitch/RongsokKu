<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\SengketaRequest;
use App\Http\Requests\Warga\UlasanRequest;
use App\Models\PermintaanJemput;
use App\Services\PermintaanJemputService;
use App\Services\SengketaService;
use App\Services\UlasanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermintaanController extends Controller
{
    public function __construct(private PermintaanJemputService $service) {}

    public function index(Request $request): View
    {
        $status = StatusPermintaan::tryFrom((string) $request->query('status'));

        $permintaan = $request->user()->permintaan()
            ->with(['pengepul', 'item.kategori', 'ulasan'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $jumlahPerStatus = $request->user()->permintaan()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('warga.permintaan.index', compact('permintaan', 'status', 'jumlahPerStatus'));
    }

    public function show(PermintaanJemput $permintaan): View
    {
        $this->authorize('view', $permintaan);

        $permintaan->load(['pengepul.user.wilayah', 'item.kategori', 'ulasan', 'sengketa', 'wilayah.induk']);

        return view('warga.permintaan.show', compact('permintaan'));
    }

    public function batal(Request $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['nullable', 'string', 'max:255']]);

        $this->service->batalOlehWarga($permintaan, $request->user(), $data['alasan'] ?? null);

        return back()->with('sukses', 'Permintaan dibatalkan.');
    }

    public function konfirmasi(Request $request, PermintaanJemput $permintaan): RedirectResponse
    {
        $this->service->konfirmasi($permintaan, $request->user());

        return back()->with('sukses', 'Transaksi selesai. Terima kasih sudah ikut mendaur ulang! Jangan lupa beri ulasan.');
    }

    public function sengketa(SengketaRequest $request, PermintaanJemput $permintaan, SengketaService $sengketa): RedirectResponse
    {
        $sengketa->ajukan(
            $permintaan,
            $request->user(),
            $request->validated('alasan'),
            $request->validated('deskripsi'),
            $request->file('bukti'),
        );

        return back()->with('sukses', 'Keberatan Anda terkirim. Admin akan meninjau dan menghubungi kedua pihak.');
    }

    public function ulasan(UlasanRequest $request, PermintaanJemput $permintaan, UlasanService $ulasan): RedirectResponse
    {
        $ulasan->kirim(
            $permintaan,
            $request->user(),
            (int) $request->validated('rating'),
            $request->validated('komentar'),
        );

        return back()->with('sukses', 'Terima kasih atas ulasan Anda.');
    }
}
