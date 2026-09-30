<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Models\ProfilPengepul;
use App\Services\VerifikasiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function __construct(private VerifikasiService $service) {}

    public function index(Request $request): View
    {
        $status = StatusVerifikasi::tryFrom((string) $request->query('status', 'menunggu'));

        $pengepul = ProfilPengepul::with('user.wilayah')
            ->when($status, fn ($q) => $q->where('status_verifikasi', $status))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $jumlah = ProfilPengepul::selectRaw('status_verifikasi, COUNT(*) as jumlah')
            ->groupBy('status_verifikasi')
            ->pluck('jumlah', 'status_verifikasi');

        return view('admin.verifikasi.index', compact('pengepul', 'status', 'jumlah'));
    }

    public function show(ProfilPengepul $pengepul): View
    {
        $pengepul->load(['user.wilayah.induk']);

        return view('admin.verifikasi.show', compact('pengepul'));
    }

    public function setujui(Request $request, ProfilPengepul $pengepul): RedirectResponse
    {
        $this->service->setujui($pengepul, $request->user());

        return redirect()->route('admin.verifikasi.index')
            ->with('sukses', "{$pengepul->nama_usaha} terverifikasi dan kini tampil di pencarian warga.");
    }

    public function tolak(Request $request, ProfilPengepul $pengepul): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:255']]);

        $this->service->tolak($pengepul, $request->user(), $data['alasan']);

        return redirect()->route('admin.verifikasi.index')
            ->with('info', "Verifikasi {$pengepul->nama_usaha} ditolak. Pengepul bisa mengajukan ulang.");
    }
}
