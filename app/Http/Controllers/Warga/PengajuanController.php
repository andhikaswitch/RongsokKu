<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\KirimPengajuanRequest;
use App\Http\Requests\Warga\TambahBarangRequest;
use App\Models\ProfilPengepul;
use App\Models\Wilayah;
use App\Services\KeranjangPengajuanService;
use App\Services\PermintaanJemputService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Wizard pengajuan penjemputan tiga langkah, tanpa JavaScript:
 * 1. pilih pengepul (atau permintaan terbuka) → 2. tambah barang → 3. alamat & jadwal.
 */
class PengajuanController extends Controller
{
    public function __construct(private KeranjangPengajuanService $keranjang) {}

    /** Langkah 1: titik masuk dari profil pengepul atau pilihan permintaan terbuka. */
    public function mulai(Request $request): View|RedirectResponse
    {
        if ($slug = $request->query('pengepul')) {
            $pengepul = ProfilPengepul::sudahTerverifikasi()->where('slug', $slug)->firstOrFail();
            $this->keranjang->mulaiLangsung($pengepul);

            return redirect()->route('warga.ajukan.barang');
        }

        if ($request->boolean('terbuka')) {
            $this->keranjang->mulaiTerbuka();

            return redirect()->route('warga.ajukan.barang');
        }

        return view('warga.ajukan.mulai', [
            'lanjutkan' => $this->keranjang->ada() && ! empty($this->keranjang->ambil()['item']),
        ]);
    }

    /** Langkah 2: tambah barang satu per satu. */
    public function barang(): View|RedirectResponse
    {
        if (! $this->keranjang->ada()) {
            return redirect()->route('warga.ajukan');
        }

        return view('warga.ajukan.barang', [
            'pengepul' => $this->keranjang->pengepul(),
            'terbuka' => $this->keranjang->terbuka(),
            'kategori' => $this->keranjang->kategoriTersedia(),
            'rincian' => $this->keranjang->rincian(),
        ]);
    }

    public function tambahBarang(TambahBarangRequest $request): RedirectResponse
    {
        $this->keranjang->tambah(
            (int) $request->validated('kategori_sampah_id'),
            (float) $request->validated('estimasi_berat'),
            $request->file('foto'),
            $request->validated('catatan'),
        );

        return redirect()->route('warga.ajukan.barang')->with('sukses', 'Barang ditambahkan ke pengajuan.');
    }

    public function hapusBarang(int $indeks): RedirectResponse
    {
        $this->keranjang->hapus($indeks);

        return redirect()->route('warga.ajukan.barang')->with('info', 'Barang dihapus dari pengajuan.');
    }

    /** Langkah 3: alamat, jadwal, dan ringkasan. */
    public function jadwal(): View|RedirectResponse
    {
        if (! $this->keranjang->ada()) {
            return redirect()->route('warga.ajukan');
        }

        if ($this->keranjang->rincian()->isEmpty()) {
            return redirect()->route('warga.ajukan.barang')->with('peringatan', 'Tambahkan minimal satu barang dulu.');
        }

        return view('warga.ajukan.jadwal', [
            'pengepul' => $this->keranjang->pengepul(),
            'terbuka' => $this->keranjang->terbuka(),
            'rincian' => $this->keranjang->rincian(),
            'kelurahan' => Wilayah::kelurahan()->with('induk')->orderBy('induk_id')->orderBy('nama')->get()
                ->groupBy(fn ($w) => $w->induk?->nama ?? 'Lainnya'),
        ]);
    }

    public function kirim(KirimPengajuanRequest $request, PermintaanJemputService $service): RedirectResponse
    {
        $permintaan = $service->kirim($request->user(), $this->keranjang->ambil(), $request->validated());

        // Foto sudah menjadi milik permintaan, jadi jangan ikut dihapus.
        $this->keranjang->kosongkan(hapusFoto: false);

        return redirect()
            ->route('warga.permintaan.show', $permintaan)
            ->with('sukses', "Permintaan {$permintaan->kode} terkirim. Kami kabari begitu pengepul merespons.");
    }

    public function batal(): RedirectResponse
    {
        $this->keranjang->kosongkan();

        return redirect()->route('warga.dashboard')->with('info', 'Pengajuan dibatalkan.');
    }
}
