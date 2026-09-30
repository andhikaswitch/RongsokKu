<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use App\Models\ProfilPengepul;
use App\Models\Wilayah;
use App\Services\PengepulSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PengepulPublikController extends Controller
{
    public function __construct(private PengepulSearchService $pencarian) {}

    public function index(Request $request): View
    {
        $filter = $request->only(['q', 'wilayah', 'kategori', 'urut', 'b3', 'buka']);

        $pengepul = $this->pencarian->cari($filter);

        $kecamatan = Wilayah::kecamatan()->orderBy('nama')->pluck('nama', 'id');
        $kategori = KategoriSampah::turunan()->aktif()->orderBy('induk_id')->orderBy('urutan')->pluck('nama', 'id');

        return view('publik.pengepul-cari', compact('pengepul', 'filter', 'kecamatan', 'kategori'));
    }

    public function show(ProfilPengepul $pengepul): View
    {
        abort_unless($pengepul->terverifikasi(), 404);

        $pengepul->load([
            'user.wilayah.induk',
            'harga' => fn ($q) => $q->where('sedang_menerima', true)->with('kategori.induk'),
        ]);

        $hargaPerGolongan = $pengepul->harga
            ->sortBy(fn ($h) => [$h->kategori->induk?->urutan, $h->kategori->urutan])
            ->groupBy(fn ($h) => $h->kategori->induk?->nama ?? 'Lainnya');

        $ulasan = $pengepul->ulasan()
            ->with('warga')
            ->whereNotNull('komentar')
            ->latest()
            ->paginate(5);

        $sebaranRating = $pengepul->ulasan()
            ->selectRaw('rating, COUNT(*) as jumlah')
            ->groupBy('rating')
            ->pluck('jumlah', 'rating');

        return view('publik.pengepul-detail', compact(
            'pengepul', 'hargaPerGolongan', 'ulasan', 'sebaranRating'
        ));
    }
}
