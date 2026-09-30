<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sengketa;
use App\Services\SengketaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SengketaController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['menunggu', 'selesai'], true) ? $request->query('status') : 'menunggu';

        $sengketa = Sengketa::with(['permintaan.pengepul', 'pelapor'])
            ->where('status', $status)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $jumlah = Sengketa::selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('admin.sengketa.index', compact('sengketa', 'status', 'jumlah'));
    }

    public function show(Sengketa $sengketa): View
    {
        $sengketa->load(['permintaan.item.kategori', 'permintaan.pengepul.user', 'permintaan.warga', 'pelapor', 'penangan']);

        return view('admin.sengketa.show', [
            'sengketa' => $sengketa,
            'keputusan' => SengketaService::KEPUTUSAN,
        ]);
    }

    public function putuskan(Request $request, Sengketa $sengketa, SengketaService $service): RedirectResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', Rule::in(array_keys(SengketaService::KEPUTUSAN))],
            'resolusi' => ['required', 'string', 'max:1000'],
            'sanksi' => ['nullable', 'boolean'],
        ], [], ['keputusan' => 'keputusan', 'resolusi' => 'catatan keputusan']);

        $service->putuskan($sengketa, $request->user(), $data['keputusan'], $data['resolusi'], $request->boolean('sanksi'));

        return redirect()->route('admin.sengketa.index')->with('sukses', "Sengketa {$sengketa->kode} sudah diputuskan.");
    }
}
