<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengaturanRequest;
use App\Models\LogAudit;
use App\Models\PengaturanPlatform;
use App\Services\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function edit(): View
    {
        $grup = PengaturanPlatform::orderBy('id')->get()->groupBy('grup');

        return view('admin.pengaturan', compact('grup'));
    }

    public function update(PengaturanRequest $request, AuditService $audit): RedirectResponse
    {
        $lama = PengaturanPlatform::pluck('nilai', 'kunci')->all();
        $baru = [];

        foreach ($request->validated('pengaturan') as $kunci => $nilai) {
            $baris = PengaturanPlatform::where('kunci', $kunci)->first();

            if ($baris && (string) $baris->nilai !== (string) $nilai) {
                $baris->update(['nilai' => $nilai]);
                $baru[$kunci] = $nilai;
            }
        }

        if ($baru) {
            $audit->catat('pengaturan.diubah', null, array_intersect_key($lama, $baru), $baru);
        }

        return back()->with('sukses', $baru ? count($baru).' pengaturan diperbarui.' : 'Tidak ada perubahan.');
    }

    public function log(Request $request): View
    {
        $log = LogAudit::with('user')
            ->when($request->query('aksi'), fn ($q, $aksi) => $q->where('aksi', 'like', "{$aksi}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $jenisAksi = LogAudit::selectRaw("DISTINCT aksi")->orderBy('aksi')->pluck('aksi')
            ->map(fn ($a) => explode('.', $a)[0])->unique()->values();

        return view('admin.log', compact('log', 'jenisAksi'));
    }
}
