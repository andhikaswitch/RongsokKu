<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Models\PermintaanJemput;
use App\Models\ProfilPengepul;
use App\Services\LaporanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    public function __construct(private LaporanService $laporan) {}

    public function index(Request $request): View
    {
        $filter = $request->only(['status', 'pengepul', 'dari', 'sampai', 'q']);

        $transaksi = $this->laporan->queryTransaksi($filter)
            ->with(['warga', 'pengepul'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $ringkasan = $this->laporan->ringkasanTransaksi($filter);

        $pengepul = ProfilPengepul::orderBy('nama_usaha')->pluck('nama_usaha', 'id');

        return view('admin.transaksi.index', compact('transaksi', 'filter', 'pengepul', 'ringkasan'));
    }

    public function show(PermintaanJemput $permintaan): View
    {
        $permintaan->load(['warga.wilayah', 'pengepul.user', 'item.kategori', 'ulasan', 'sengketa', 'wilayah.induk']);

        $mutasi = \App\Models\MutasiSaldo::where('referensi_type', PermintaanJemput::class)
            ->where('referensi_id', $permintaan->id)
            ->get();

        return view('admin.transaksi.show', compact('permintaan', 'mutasi'));
    }

    public function ekspor(Request $request): StreamedResponse
    {
        return $this->laporan->eksporCsv($request->only(['status', 'pengepul', 'dari', 'sampai', 'q']));
    }
}
