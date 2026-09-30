<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipeSumberHarga;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SumberHargaRequest;
use App\Models\IndeksHarga;
use App\Models\KategoriSampah;
use App\Models\SumberHargaPasar;
use App\Services\AuditService;
use App\Services\IndeksHargaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HargaController extends Controller
{
    public function __construct(private AuditService $audit) {}

    /** Dua tab lewat query string: acuan resmi dan indeks transaksi. */
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'indeks' ? 'indeks' : 'acuan';

        $acuan = SumberHargaPasar::with(['kategori.induk', 'pencatat'])
            ->latest('berlaku_mulai')
            ->paginate(20)
            ->withQueryString();

        $indeks = IndeksHarga::with('kategori.induk')
            ->whereNull('wilayah_id')
            ->whereDate('tanggal', now()->toDateString())
            ->orderByDesc('jumlah_transaksi')
            ->get();

        $terakhirDihitung = IndeksHarga::max('updated_at');

        return view('admin.harga.index', compact('tab', 'acuan', 'indeks', 'terakhirDihitung'));
    }

    public function create(): View
    {
        return view('admin.harga.form', [
            'sumber' => new SumberHargaPasar(['berlaku_mulai' => now()]),
            'kategori' => $this->daftarKategori(),
            'tipe' => $this->daftarTipe(),
        ]);
    }

    public function store(SumberHargaRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('dokumen_bukti');
        $data['dicatat_oleh'] = $request->user()->id;

        if ($berkas = $request->file('dokumen_bukti')) {
            // Dokumen acuan sengaja publik agar warga bisa menelusuri sumbernya.
            $data['dokumen_bukti'] = $berkas->store('dokumen-harga', 'public');
        }

        $sumber = SumberHargaPasar::create($data);
        $this->sinkronkanAcuanKategori($sumber);
        $this->audit->catat('harga_acuan.dibuat', $sumber, [], $sumber->only('harga_min', 'harga_max', 'sumber_nama'));

        return redirect()->route('admin.harga.index')->with('sukses', 'Harga acuan tercatat dan langsung tampil di Pusat Harga.');
    }

    public function edit(SumberHargaPasar $sumber): View
    {
        return view('admin.harga.form', [
            'sumber' => $sumber,
            'kategori' => $this->daftarKategori(),
            'tipe' => $this->daftarTipe(),
        ]);
    }

    public function update(SumberHargaRequest $request, SumberHargaPasar $sumber): RedirectResponse
    {
        $lama = $sumber->only('harga_min', 'harga_max', 'sumber_nama');
        $data = $request->safe()->except('dokumen_bukti');

        if ($berkas = $request->file('dokumen_bukti')) {
            if ($sumber->dokumen_bukti) {
                Storage::disk('public')->delete($sumber->dokumen_bukti);
            }
            $data['dokumen_bukti'] = $berkas->store('dokumen-harga', 'public');
        }

        $sumber->update($data);
        $this->sinkronkanAcuanKategori($sumber);
        $this->audit->catat('harga_acuan.diubah', $sumber, $lama, $sumber->only(array_keys($lama)));

        return redirect()->route('admin.harga.index')->with('sukses', 'Harga acuan diperbarui.');
    }

    public function destroy(SumberHargaPasar $sumber): RedirectResponse
    {
        if ($sumber->dokumen_bukti) {
            Storage::disk('public')->delete($sumber->dokumen_bukti);
        }

        $this->audit->catat('harga_acuan.dihapus', null, $sumber->only('id', 'sumber_nama', 'harga_min', 'harga_max'));
        $sumber->delete();

        return back()->with('sukses', 'Harga acuan dihapus.');
    }

    public function hitungUlang(IndeksHargaService $indeks): RedirectResponse
    {
        $jumlah = $indeks->hitungSemua();
        $this->audit->catat('indeks.dihitung_ulang', null, [], ['kategori' => $jumlah]);

        return redirect()->route('admin.harga.index', ['tab' => 'indeks'])
            ->with('sukses', "Indeks dihitung ulang untuk {$jumlah} kategori.");
    }

    /** Rentang acuan terbaru juga disalin ke kategori sebagai panduan pengepul. */
    private function sinkronkanAcuanKategori(SumberHargaPasar $sumber): void
    {
        $terbaru = $sumber->kategori->acuanBerlaku();

        if ($terbaru) {
            $sumber->kategori->update([
                'harga_acuan_min' => $terbaru->harga_min,
                'harga_acuan_max' => $terbaru->harga_max,
            ]);
        }
    }

    private function daftarKategori(): array
    {
        return KategoriSampah::turunan()->with('induk')->orderBy('induk_id')->orderBy('urutan')->get()
            ->groupBy(fn ($k) => $k->induk?->nama ?? 'Lainnya')
            ->map(fn ($g) => $g->pluck('nama', 'id'))
            ->all();
    }

    private function daftarTipe(): array
    {
        return collect(TipeSumberHarga::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all();
    }
}
