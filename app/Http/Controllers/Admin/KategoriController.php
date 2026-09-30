<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KategoriRequest;
use App\Models\KategoriSampah;
use App\Services\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(): View
    {
        $golongan = KategoriSampah::utama()
            ->with(['anak' => fn ($q) => $q->withCount('hargaPengepul')])
            ->orderBy('urutan')
            ->get();

        return view('admin.kategori.index', compact('golongan'));
    }

    public function create(): View
    {
        return view('admin.kategori.form', [
            'kategori' => new KategoriSampah(['satuan' => 'kg', 'aktif' => true, 'induk_id' => request('induk')]),
            'induk' => KategoriSampah::utama()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function store(KategoriRequest $request): RedirectResponse
    {
        $kategori = KategoriSampah::create($this->data($request));
        $this->audit->catat('kategori.dibuat', $kategori, [], $kategori->only('nama', 'induk_id'));

        return redirect()->route('admin.kategori.index')->with('sukses', "Kategori {$kategori->nama} ditambahkan.");
    }

    public function edit(KategoriSampah $kategori): View
    {
        return view('admin.kategori.form', [
            'kategori' => $kategori,
            'induk' => KategoriSampah::utama()->whereKeyNot($kategori->id)->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function update(KategoriRequest $request, KategoriSampah $kategori): RedirectResponse
    {
        $lama = $kategori->only('nama', 'harga_acuan_min', 'harga_acuan_max', 'aktif', 'limbah_b3');
        $kategori->update($this->data($request, $kategori));
        $this->audit->catat('kategori.diubah', $kategori, $lama, $kategori->only(array_keys($lama)));

        return redirect()->route('admin.kategori.index')->with('sukses', "Kategori {$kategori->nama} diperbarui.");
    }

    public function destroy(KategoriSampah $kategori): RedirectResponse
    {
        // Kategori yang pernah ditransaksikan tidak boleh dihapus agar riwayat
        // tetap utuh; cukup dinonaktifkan.
        if ($kategori->anak()->exists() || \App\Models\ItemPermintaan::where('kategori_sampah_id', $kategori->id)->exists()) {
            $kategori->update(['aktif' => false]);

            return back()->with('peringatan', "{$kategori->nama} sudah dipakai dalam transaksi, jadi dinonaktifkan alih-alih dihapus.");
        }

        $this->audit->catat('kategori.dihapus', null, $kategori->only('id', 'nama'));
        $kategori->delete();

        return back()->with('sukses', 'Kategori dihapus.');
    }

    private function data(KategoriRequest $request, ?KategoriSampah $kategori = null): array
    {
        $data = $request->validated();
        $data['limbah_b3'] = $request->boolean('limbah_b3');
        $data['aktif'] = $request->boolean('aktif');
        $data['urutan'] = $data['urutan'] ?? 0;

        if (! $kategori || $kategori->nama !== $data['nama']) {
            $slug = Str::slug($data['nama']);
            $data['slug'] = KategoriSampah::where('slug', $slug)->whereKeyNot($kategori?->id)->exists()
                ? $slug.'-'.Str::lower(Str::random(4))
                : $slug;
        }

        return $data;
    }
}
