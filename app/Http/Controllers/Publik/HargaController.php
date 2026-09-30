<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\IndeksHarga;
use App\Models\KategoriSampah;
use App\Services\IndeksHargaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HargaController extends Controller
{
    public function __construct(private IndeksHargaService $indeks) {}

    /** Pusat Harga Pasar: indeks transaksi nyata + acuan resmi. */
    public function index(Request $request): View
    {
        $golonganDipilih = $request->query('golongan');

        $golongan = KategoriSampah::utama()->aktif()->orderBy('urutan')->get();

        $kategori = KategoriSampah::turunan()->aktif()
            ->with('induk')
            ->when($golonganDipilih, fn ($q) => $q->whereHas('induk', fn ($i) => $i->where('slug', $golonganDipilih)))
            ->orderBy('induk_id')
            ->orderBy('urutan')
            ->get();

        $hariIni = now()->toDateString();

        $indeksTerbaru = IndeksHarga::whereNull('wilayah_id')
            ->whereDate('tanggal', $hariIni)
            ->get()
            ->keyBy('kategori_sampah_id');

        // Riwayat 14 hari untuk sparkline, diambil sekali lalu dikelompokkan.
        $riwayat = IndeksHarga::whereNull('wilayah_id')
            ->where('tanggal', '>=', now()->subDays(14)->toDateString())
            ->orderBy('tanggal')
            ->get()
            ->groupBy('kategori_sampah_id');

        $acuan = \App\Models\SumberHargaPasar::with('kategori')
            ->where('berlaku_mulai', '<=', now())
            ->where(fn ($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now()))
            ->get()
            ->keyBy('kategori_sampah_id');

        return view('publik.harga', compact(
            'golongan', 'golonganDipilih', 'kategori', 'indeksTerbaru', 'riwayat', 'acuan'
        ));
    }

    /** Rincian satu kategori beserta daftar pengepul yang menerimanya. */
    public function show(KategoriSampah $kategori): View
    {
        $kategori->load('induk');

        $indeks = $kategori->indeksTerbaru();
        $riwayat = $this->indeks->riwayat($kategori, 30);
        $acuan = $kategori->acuanBerlaku();

        $pengepul = \App\Models\HargaPengepul::with('pengepul.user.wilayah.induk')
            ->where('kategori_sampah_id', $kategori->id)
            ->where('sedang_menerima', true)
            ->whereHas('pengepul', fn ($q) => $q->sudahTerverifikasi())
            ->orderByDesc('harga_per_satuan')
            ->take(12)
            ->get();

        return view('publik.harga-detail', compact('kategori', 'indeks', 'riwayat', 'acuan', 'pengepul'));
    }
}
