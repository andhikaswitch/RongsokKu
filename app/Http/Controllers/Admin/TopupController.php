<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusTopup;
use App\Http\Controllers\Controller;
use App\Models\PermintaanTopup;
use App\Services\TopupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    public function __construct(private TopupService $service) {}

    public function index(Request $request): View
    {
        $status = StatusTopup::tryFrom((string) $request->query('status', 'menunggu'));

        $topup = PermintaanTopup::with('pengepul')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $jumlah = PermintaanTopup::selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('admin.topup.index', compact('topup', 'status', 'jumlah'));
    }

    public function show(PermintaanTopup $topup): View
    {
        $topup->load(['pengepul.user', 'verifikator']);

        return view('admin.topup.show', compact('topup'));
    }

    public function setujui(Request $request, PermintaanTopup $topup): RedirectResponse
    {
        $this->service->setujui($topup, $request->user());

        return redirect()->route('admin.topup.index')
            ->with('sukses', "Top-up {$topup->kode} disetujui. Saldo ".rupiah($topup->jumlah).' sudah masuk.');
    }

    public function tolak(Request $request, PermintaanTopup $topup): RedirectResponse
    {
        $data = $request->validate(['catatan' => ['required', 'string', 'max:255']]);

        $this->service->tolak($topup, $request->user(), $data['catatan']);

        return redirect()->route('admin.topup.index')->with('info', "Top-up {$topup->kode} ditolak.");
    }
}
