<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeranPengguna;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PenggunaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function __construct(private PenggunaService $service) {}

    public function index(Request $request): View
    {
        $peran = PeranPengguna::tryFrom((string) $request->query('peran'));
        $status = $request->query('status');

        $pengguna = User::with(['wilayah', 'profilPengepul'])
            ->when($peran, fn ($q) => $q->where('peran', $peran))
            ->when($status === 'aktif', fn ($q) => $q->where('aktif', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('aktif', false))
            ->when($request->query('q'), fn ($q, $cari) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('email', 'like', "%{$cari}%")
                ->orWhere('telepon', 'like', "%{$cari}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $jumlah = User::selectRaw('peran, COUNT(*) as jumlah')->groupBy('peran')->pluck('jumlah', 'peran');

        return view('admin.pengguna.index', compact('pengguna', 'peran', 'status', 'jumlah'));
    }

    public function show(User $pengguna): View
    {
        $pengguna->load(['wilayah.induk', 'profilPengepul', 'lencana']);

        $permintaan = $pengguna->isPengepul()
            ? $pengguna->profilPengepul?->permintaan()->with('warga')->latest()->take(10)->get()
            : $pengguna->permintaan()->with('pengepul')->latest()->take(10)->get();

        return view('admin.pengguna.show', ['pengguna' => $pengguna, 'permintaan' => $permintaan ?? collect()]);
    }

    public function nonaktifkan(Request $request, User $pengguna): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:255']]);

        $this->service->nonaktifkan($pengguna, $data['alasan']);

        return back()->with('sukses', "Akun {$pengguna->name} dinonaktifkan.");
    }

    public function aktifkan(User $pengguna): RedirectResponse
    {
        $this->service->aktifkan($pengguna);

        return back()->with('sukses', "Akun {$pengguna->name} diaktifkan kembali.");
    }

    public function aturUlangSandi(User $pengguna): RedirectResponse
    {
        $sandi = $this->service->aturUlangSandi($pengguna);

        return back()->with('info', "Kata sandi sementara untuk {$pengguna->name}: {$sandi} — sampaikan langsung ke pemilik akun.");
    }
}
