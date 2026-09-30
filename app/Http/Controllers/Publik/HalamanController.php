<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PeranPengguna;
use App\Enums\StatusPermintaan;
use App\Http\Controllers\Controller;
use App\Models\Lencana;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HalamanController extends Controller
{
    public function caraKerja(): View
    {
        return view('publik.cara-kerja');
    }

    public function tentang(): View
    {
        return view('publik.tentang');
    }

    public function faq(): View
    {
        return view('publik.faq');
    }

    /** Papan peringkat warga paling rajin mendaur ulang. */
    public function peringkat(Request $request): View
    {
        $wilayahDipilih = $request->query('wilayah');

        $peringkat = User::query()
            ->where('users.peran', PeranPengguna::Warga)
            ->join('permintaan_jemput', 'permintaan_jemput.warga_id', '=', 'users.id')
            ->where('permintaan_jemput.status', StatusPermintaan::Selesai->value)
            ->when($wilayahDipilih, fn ($q) => $q->where('users.wilayah_id', $wilayahDipilih))
            ->groupBy('users.id', 'users.name', 'users.foto_profil', 'users.wilayah_id')
            ->select('users.id', 'users.name', 'users.foto_profil', 'users.wilayah_id')
            ->selectRaw('SUM(permintaan_jemput.berat_final_kg) as total_berat')
            ->selectRaw('COUNT(permintaan_jemput.id) as total_transaksi')
            ->orderByDesc('total_berat')
            ->with('wilayah.induk')
            ->take(20)
            ->get();

        $kelurahan = Wilayah::kelurahan()->with('induk')->orderBy('nama')->get()
            ->mapWithKeys(fn ($w) => [$w->id => $w->namaLengkap()]);

        $lencana = Lencana::orderBy('urutan')->get();

        // Total daur ulang per kelurahan, untuk kompetisi antar wilayah.
        $perWilayah = DB::table('permintaan_jemput')
            ->join('users', 'users.id', '=', 'permintaan_jemput.warga_id')
            ->join('wilayah', 'wilayah.id', '=', 'users.wilayah_id')
            ->where('permintaan_jemput.status', StatusPermintaan::Selesai->value)
            ->groupBy('wilayah.id', 'wilayah.nama')
            ->select('wilayah.nama')
            ->selectRaw('SUM(permintaan_jemput.berat_final_kg) as total_berat')
            ->selectRaw('COUNT(DISTINCT users.id) as jumlah_warga')
            ->orderByDesc('total_berat')
            ->take(8)
            ->get();

        return view('publik.peringkat', compact('peringkat', 'kelurahan', 'wilayahDipilih', 'lencana', 'perWilayah'));
    }
}
