<?php

namespace App\Http\Controllers\Pengepul;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PunyaProfilPengepul;
use App\Http\Requests\Pengepul\SimpanHargaRequest;
use App\Models\IndeksHarga;
use App\Models\KategoriSampah;
use App\Services\HargaPengepulService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HargaController extends Controller
{
    use PunyaProfilPengepul;

    public function index(): View
    {
        $profil = $this->profil()->load('harga');
        $hargaSaya = $profil->harga->keyBy('kategori_sampah_id');

        $golongan = KategoriSampah::utama()->aktif()
            ->with(['anak' => fn ($q) => $q->aktif()->with(['sumberHarga' => fn ($s) => $s->latest('berlaku_mulai')])])
            ->orderBy('urutan')
            ->get();

        $indeks = IndeksHarga::whereNull('wilayah_id')
            ->whereDate('tanggal', now()->toDateString())
            ->where('jumlah_transaksi', '>=', IndeksHarga::MIN_TRANSAKSI)
            ->pluck('harga_median', 'kategori_sampah_id');

        $ambang = (float) pengaturan('ambang_peringatan_harga', 15);

        return view('pengepul.harga.index', compact('profil', 'hargaSaya', 'golongan', 'indeks', 'ambang'));
    }

    public function simpan(SimpanHargaRequest $request, HargaPengepulService $service): RedirectResponse
    {
        $jumlah = $service->simpan($this->profil(), $request->validated('harga', []));

        return back()->with('sukses', "Daftar harga disimpan. {$jumlah} kategori sedang Anda pasang.");
    }
}
