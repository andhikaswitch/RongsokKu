<?php

namespace App\Services;

use App\Enums\JenisMutasi;
use App\Enums\StatusPermintaan;
use App\Exceptions\AksiTidakValid;
use App\Models\HargaPengepul;
use App\Models\IndeksHarga;
use App\Models\ItemPermintaan;
use App\Models\KategoriSampah;
use App\Models\PermintaanJemput;
use App\Models\ProfilPengepul;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\Haversine;
use Illuminate\Support\Facades\DB;

/**
 * Mengatur seluruh perjalanan status sebuah permintaan jemput.
 *
 *   DIAJUKAN ──► DIJADWALKAN ──► DIJEMPUT ──► MENUNGGU_KONFIRMASI ──► SELESAI
 *
 * Setiap perpindahan status mengunci baris permintaan lebih dulu, sehingga
 * dua aksi yang datang bersamaan (misalnya dua pengepul mengklaim permintaan
 * terbuka yang sama) tidak bisa sama-sama berhasil.
 */
class PermintaanJemputService
{
    public function __construct(
        private WalletService $dompet,
        private MetrikPengepulService $metrik,
        private LencanaService $lencana,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Pengajuan oleh warga
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array{mode: string, profil_pengepul_id: ?int, item: array<int, array>}  $keranjang
     * @param  array{alamat_jemput: string, wilayah_id: int, jadwal_tanggal: string, jadwal_sesi: string, catatan_warga: ?string}  $data
     */
    public function kirim(User $warga, array $keranjang, array $data): PermintaanJemput
    {
        if (empty($keranjang['item'])) {
            throw new AksiTidakValid('Tambahkan minimal satu jenis barang sebelum mengirim permintaan.');
        }

        $terbuka = ($keranjang['mode'] ?? 'langsung') === 'terbuka';
        $pengepul = $terbuka ? null : ProfilPengepul::with(['harga', 'user'])->find($keranjang['profil_pengepul_id'] ?? 0);

        if (! $terbuka) {
            $this->pastikanPengepulMenerima($pengepul);
        }

        $wilayah = Wilayah::findOrFail($data['wilayah_id']);

        // Koordinat alamat penjemputan mengikuti profil warga bila kelurahannya
        // sama; bila warga memilih kelurahan lain, pakai titik kelurahan itu.
        $lat = (int) $warga->wilayah_id === (int) $wilayah->id && $warga->latitude
            ? (float) $warga->latitude : (float) $wilayah->latitude;
        $lng = (int) $warga->wilayah_id === (int) $wilayah->id && $warga->longitude
            ? (float) $warga->longitude : (float) $wilayah->longitude;

        return DB::transaction(function () use ($warga, $keranjang, $data, $terbuka, $pengepul, $wilayah, $lat, $lng) {
            $permintaan = PermintaanJemput::create([
                'kode' => $this->kodeBaru(),
                'warga_id' => $warga->id,
                'profil_pengepul_id' => $pengepul?->id,
                'permintaan_terbuka' => $terbuka,
                'status' => StatusPermintaan::Diajukan,
                'wilayah_id' => $wilayah->id,
                'alamat_jemput' => $data['alamat_jemput'],
                'latitude' => $lat,
                'longitude' => $lng,
                'jarak_km' => $pengepul?->user?->latitude
                    ? Haversine::jarak((float) $pengepul->user->latitude, (float) $pengepul->user->longitude, $lat, $lng)
                    : null,
                'jadwal_tanggal' => $data['jadwal_tanggal'],
                'jadwal_sesi' => $data['jadwal_sesi'],
                'catatan_warga' => $data['catatan_warga'] ?? null,
            ]);

            $totalEstimasi = 0;
            $totalBerat = 0;

            foreach ($keranjang['item'] as $baris) {
                $kategori = KategoriSampah::findOrFail($baris['kategori_sampah_id']);
                $berat = round((float) $baris['estimasi_berat'], 2);

                // Harga dibekukan di sini. Perubahan daftar harga pengepul
                // setelah detik ini tidak akan memengaruhi permintaan ini.
                $harga = $terbuka
                    ? $this->hargaPerkiraan($kategori)
                    : $this->hargaPengepulUntuk($pengepul, $kategori, $berat);

                $subtotal = round($berat * $harga, 2);

                ItemPermintaan::create([
                    'permintaan_jemput_id' => $permintaan->id,
                    'kategori_sampah_id' => $kategori->id,
                    'estimasi_berat' => $berat,
                    'harga_estimasi_per_satuan' => $harga,
                    'subtotal_estimasi' => $subtotal,
                    'foto_barang' => $baris['foto'] ?? null,
                    'catatan' => $baris['catatan'] ?? null,
                ]);

                $totalEstimasi += $subtotal;
                $totalBerat += $berat;
            }

            $permintaan->update([
                'estimasi_total' => round($totalEstimasi, 2),
                'estimasi_berat_kg' => round($totalBerat, 2),
            ]);

            return $permintaan;
        });
    }

    public function batalOlehWarga(PermintaanJemput $permintaan, User $warga, ?string $alasan): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($warga, $alasan) {
            $this->pastikanMilikWarga($p, $warga);
            $this->pastikanStatus($p, [StatusPermintaan::Diajukan, StatusPermintaan::Dijadwalkan],
                'Permintaan yang sudah dalam penjemputan tidak bisa dibatalkan.');

            $p->forceFill([
                'status' => StatusPermintaan::Dibatalkan,
                'dibatalkan_oleh' => 'warga',
                'alasan_pembatalan' => $alasan,
                'dibatalkan_pada' => now(),
            ])->save();
        });
    }

    /** Warga menyetujui hasil timbangan. Inilah satu-satunya jalan menuju SELESAI dari sisi warga. */
    public function konfirmasi(PermintaanJemput $permintaan, User $warga): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($warga) {
            $this->pastikanMilikWarga($p, $warga);
            $this->pastikanStatus($p, [StatusPermintaan::MenungguKonfirmasi],
                'Hasil timbangan belum tersedia untuk dikonfirmasi.');

            $this->selesaikan($p);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Tindakan pengepul
    |--------------------------------------------------------------------------
    */

    public function terima(PermintaanJemput $permintaan, ProfilPengepul $pengepul, array $jadwal = []): void
    {
        $this->pastikanSaldoCukup($pengepul);

        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul, $jadwal) {
            $this->pastikanMilikPengepul($p, $pengepul);
            $this->pastikanStatus($p, [StatusPermintaan::Diajukan], 'Permintaan ini sudah direspons sebelumnya.');

            $p->forceFill([
                'status' => StatusPermintaan::Dijadwalkan,
                'jadwal_tanggal' => $jadwal['jadwal_tanggal'] ?? $p->jadwal_tanggal,
                'jadwal_sesi' => $jadwal['jadwal_sesi'] ?? $p->jadwal_sesi,
                'catatan_pengepul' => $jadwal['catatan_pengepul'] ?? $p->catatan_pengepul,
                'diterima_pada' => now(),
            ])->save();
        });
    }

    public function tolak(PermintaanJemput $permintaan, ProfilPengepul $pengepul, string $alasan): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul, $alasan) {
            $this->pastikanMilikPengepul($p, $pengepul);
            $this->pastikanStatus($p, [StatusPermintaan::Diajukan], 'Permintaan ini sudah direspons sebelumnya.');

            $p->forceFill([
                'status' => StatusPermintaan::Ditolak,
                'alasan_penolakan' => $alasan,
            ])->save();
        });

        $this->metrik->segarkan($pengepul);
    }

    /**
     * Siapa cepat dia dapat. Baris permintaan dikunci, lalu diperiksa ulang
     * masih kosong atau tidak, sehingga dua pengepul yang menekan tombol
     * bersamaan tidak akan sama-sama mendapatkannya.
     */
    public function klaimTerbuka(PermintaanJemput $permintaan, ProfilPengepul $pengepul): void
    {
        $this->pastikanPengepulMenerima($pengepul);
        $this->pastikanSaldoCukup($pengepul);

        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul) {
            if (! $p->permintaan_terbuka || $p->profil_pengepul_id !== null || $p->status !== StatusPermintaan::Diajukan) {
                throw new AksiTidakValid('Permintaan ini sudah diklaim pengepul lain.');
            }

            $pengepul->loadMissing(['harga', 'user']);
            $total = 0;

            // Pengepul yang mengklaim menyepakati harganya sendiri saat itu,
            // dan harga itulah yang dibekukan untuk sisa perjalanan permintaan.
            foreach ($p->item()->with('kategori')->get() as $item) {
                $harga = $this->hargaPengepulUntuk($pengepul, $item->kategori, (float) $item->estimasi_berat);
                $subtotal = round((float) $item->estimasi_berat * $harga, 2);

                $item->update([
                    'harga_estimasi_per_satuan' => $harga,
                    'subtotal_estimasi' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $p->forceFill([
                'profil_pengepul_id' => $pengepul->id,
                'status' => StatusPermintaan::Dijadwalkan,
                'estimasi_total' => round($total, 2),
                'jarak_km' => $pengepul->user->latitude && $p->latitude
                    ? Haversine::jarak((float) $pengepul->user->latitude, (float) $pengepul->user->longitude, (float) $p->latitude, (float) $p->longitude)
                    : null,
                'diterima_pada' => now(),
            ])->save();
        });
    }

    public function berangkat(PermintaanJemput $permintaan, ProfilPengepul $pengepul): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul) {
            $this->pastikanMilikPengepul($p, $pengepul);
            $this->pastikanStatus($p, [StatusPermintaan::Dijadwalkan], 'Permintaan ini belum atau sudah melewati tahap penjadwalan.');

            $p->forceFill([
                'status' => StatusPermintaan::Dijemput,
                'dijemput_pada' => now(),
            ])->save();
        });
    }

    /**
     * Pengepul memasukkan hasil timbangan di lokasi.
     *
     * @param  array<int, array{berat_final: float|string, harga_final: float|string}>  $hasil  dikunci oleh id item
     */
    public function timbang(PermintaanJemput $permintaan, ProfilPengepul $pengepul, array $hasil, ?string $catatan): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul, $hasil, $catatan) {
            $this->pastikanMilikPengepul($p, $pengepul);
            $this->pastikanStatus($p, [StatusPermintaan::Dijemput], 'Tandai dulu bahwa Anda sudah berangkat menjemput.');

            $total = 0;
            $berat = 0;
            $adaHargaTurun = false;

            foreach ($p->item as $item) {
                if (! isset($hasil[$item->id])) {
                    throw new AksiTidakValid('Hasil timbangan untuk setiap barang wajib diisi.');
                }

                $beratFinal = round((float) $hasil[$item->id]['berat_final'], 2);
                $hargaFinal = round((float) $hasil[$item->id]['harga_final'], 2);

                if ($hargaFinal < (float) $item->harga_estimasi_per_satuan) {
                    $adaHargaTurun = true;
                }

                $subtotal = round($beratFinal * $hargaFinal, 2);

                $item->update([
                    'berat_final' => $beratFinal,
                    'harga_final_per_satuan' => $hargaFinal,
                    'subtotal_final' => $subtotal,
                ]);

                $total += $subtotal;
                $berat += $beratFinal;
            }

            // Menurunkan harga dari yang disepakati boleh saja, tapi harus
            // disertai alasan agar warga bisa menilai dan admin bisa menelusuri.
            if ($adaHargaTurun && blank($catatan)) {
                throw new AksiTidakValid('Harga yang Anda masukkan lebih rendah dari harga yang disepakati. Tuliskan alasannya untuk warga.');
            }

            $p->forceFill([
                'status' => StatusPermintaan::MenungguKonfirmasi,
                'total_final' => round($total, 2),
                'berat_final_kg' => round($berat, 2),
                'catatan_pengepul' => $catatan ?: $p->catatan_pengepul,
                'ditimbang_pada' => now(),
            ])->save();
        });
    }

    public function batalOlehPengepul(PermintaanJemput $permintaan, ProfilPengepul $pengepul, string $alasan): void
    {
        $this->ubah($permintaan, function (PermintaanJemput $p) use ($pengepul, $alasan) {
            $this->pastikanMilikPengepul($p, $pengepul);
            $this->pastikanStatus($p, [StatusPermintaan::Dijadwalkan, StatusPermintaan::Dijemput],
                'Hanya permintaan yang sudah dijadwalkan yang bisa dibatalkan.');

            $p->forceFill([
                'status' => StatusPermintaan::Dibatalkan,
                'dibatalkan_oleh' => 'pengepul',
                'alasan_pembatalan' => $alasan,
                'dibatalkan_pada' => now(),
            ])->save();
        });

        $this->metrik->segarkan($pengepul);
    }

    /*
    |--------------------------------------------------------------------------
    | Penyelesaian — dipakai konfirmasi warga dan keputusan sengketa admin
    |--------------------------------------------------------------------------
    */

    /**
     * Menandai transaksi selesai dan memotong komisi.
     * Harus dipanggil di dalam transaksi database yang sudah mengunci baris permintaan.
     */
    public function selesaikan(PermintaanJemput $p): void
    {
        $p->loadMissing(['item', 'pengepul', 'warga']);

        $total = round((float) $p->item->sum('subtotal_final'), 2);
        $berat = round((float) $p->item->sum('berat_final'), 2);
        $tarif = (float) pengaturan('tarif_komisi', 5);
        $komisi = round($total * $tarif / 100, 2);

        $p->forceFill([
            'status' => StatusPermintaan::Selesai,
            'total_final' => $total,
            'berat_final_kg' => $berat,
            // Tarif dibekukan agar riwayat tetap akurat walau admin mengubahnya kelak.
            'tarif_komisi' => $tarif,
            'jumlah_komisi' => $komisi,
            'selesai_pada' => now(),
        ])->save();

        if ($komisi > 0) {
            $this->dompet->debit(
                $p->pengepul,
                $komisi,
                JenisMutasi::Komisi,
                $p,
                "Komisi {$this->angka($tarif)}% transaksi {$p->kode}"
            );
        }

        $this->metrik->segarkan($p->pengepul);
        $this->lencana->periksa($p->warga);
    }

    /*
    |--------------------------------------------------------------------------
    | Bantuan harga
    |--------------------------------------------------------------------------
    */

    /** Harga pengepul untuk satu kategori, sekaligus memeriksa aturan penerimaannya. */
    public function hargaPengepulUntuk(ProfilPengepul $pengepul, KategoriSampah $kategori, ?float $berat = null): float
    {
        /** @var HargaPengepul|null $harga */
        $harga = $pengepul->harga->firstWhere('kategori_sampah_id', $kategori->id);

        if (! $harga || ! $harga->sedang_menerima) {
            throw new AksiTidakValid("{$pengepul->nama_usaha} tidak sedang menerima {$kategori->nama}.");
        }

        if ($kategori->limbah_b3 && ! $pengepul->izin_b3) {
            throw new AksiTidakValid("{$kategori->nama} tergolong limbah B3 dan hanya boleh diterima pengepul berizin.");
        }

        if ($berat !== null && $berat < (float) $harga->min_berat) {
            throw new AksiTidakValid("{$pengepul->nama_usaha} menerima {$kategori->nama} minimal ".berat($harga->min_berat).'.');
        }

        return (float) $harga->harga_per_satuan;
    }

    /**
     * Perkiraan harga untuk permintaan terbuka yang belum punya pengepul:
     * indeks pasar bila datanya cukup, jika tidak nilai tengah harga acuan.
     */
    public function hargaPerkiraan(KategoriSampah $kategori): float
    {
        $indeks = $kategori->indeksTerbaru();

        if ($indeks && $indeks->jumlah_transaksi >= IndeksHarga::MIN_TRANSAKSI) {
            return (float) $indeks->harga_median;
        }

        if ($kategori->harga_acuan_min && $kategori->harga_acuan_max) {
            return round(((float) $kategori->harga_acuan_min + (float) $kategori->harga_acuan_max) / 2, 2);
        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Penjaga aturan
    |--------------------------------------------------------------------------
    */

    /** Jalankan perubahan status di dalam transaksi dengan baris permintaan terkunci. */
    private function ubah(PermintaanJemput $permintaan, callable $aksi): void
    {
        DB::transaction(function () use ($permintaan, $aksi) {
            $terkunci = PermintaanJemput::whereKey($permintaan->getKey())->lockForUpdate()->firstOrFail();
            $terkunci->load('item');

            $aksi($terkunci);
        });

        $permintaan->refresh();
    }

    private function pastikanStatus(PermintaanJemput $p, array $diizinkan, string $pesan): void
    {
        if (! in_array($p->status, $diizinkan, true)) {
            throw new AksiTidakValid($pesan);
        }
    }

    private function pastikanMilikWarga(PermintaanJemput $p, User $warga): void
    {
        if ((int) $p->warga_id !== (int) $warga->id) {
            throw new AksiTidakValid('Permintaan ini bukan milik Anda.');
        }
    }

    private function pastikanMilikPengepul(PermintaanJemput $p, ProfilPengepul $pengepul): void
    {
        if ((int) $p->profil_pengepul_id !== (int) $pengepul->id) {
            throw new AksiTidakValid('Permintaan ini tidak ditujukan kepada Anda.');
        }
    }

    private function pastikanPengepulMenerima(?ProfilPengepul $pengepul): void
    {
        if (! $pengepul || ! $pengepul->terverifikasi()) {
            throw new AksiTidakValid('Pengepul ini belum terverifikasi.');
        }

        if (! $pengepul->sedang_menerima) {
            throw new AksiTidakValid("{$pengepul->nama_usaha} sedang tidak menerima permintaan.");
        }

        if (! $pengepul->user?->aktif) {
            throw new AksiTidakValid('Akun pengepul ini sedang dinonaktifkan.');
        }
    }

    private function pastikanSaldoCukup(ProfilPengepul $pengepul): void
    {
        if (! $pengepul->saldoCukup()) {
            throw new AksiTidakValid('Saldo Anda di bawah '.rupiah(pengaturan('saldo_minimum', 10000))
                .'. Isi saldo dulu untuk menerima permintaan baru.');
        }
    }

    private function kodeBaru(): string
    {
        $terakhir = (int) PermintaanJemput::lockForUpdate()->max('id');

        do {
            $kode = 'RSK-'.str_pad((string) ++$terakhir, 6, '0', STR_PAD_LEFT);
        } while (PermintaanJemput::where('kode', $kode)->exists());

        return $kode;
    }

    private function angka(float $nilai): string
    {
        return rtrim(rtrim(number_format($nilai, 2, ',', '.'), '0'), ',');
    }
}
