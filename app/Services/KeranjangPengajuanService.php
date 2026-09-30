<?php

namespace App\Services;

use App\Exceptions\AksiTidakValid;
use App\Models\KategoriSampah;
use App\Models\ProfilPengepul;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Draf pengajuan penjemputan yang disimpan di session.
 *
 * Tanpa JavaScript, formulir tidak bisa menambah baris barang secara dinamis.
 * Karena itu tiap barang dikirim satu per satu lewat POST biasa dan
 * dikumpulkan di sini, lalu baru dikirim sekaligus di langkah terakhir.
 */
class KeranjangPengajuanService
{
    private const KUNCI = 'pengajuan';

    public function __construct(private PermintaanJemputService $permintaan) {}

    public function mulaiLangsung(ProfilPengepul $pengepul): void
    {
        $sekarang = $this->ambil();

        // Pindah ke pengepul lain mengosongkan keranjang, karena daftar
        // kategori dan harga yang berlaku ikut berubah.
        if (($sekarang['profil_pengepul_id'] ?? null) !== $pengepul->id) {
            $this->kosongkan();
            session([self::KUNCI => ['mode' => 'langsung', 'profil_pengepul_id' => $pengepul->id, 'item' => []]]);
        }
    }

    public function mulaiTerbuka(): void
    {
        if (($this->ambil()['mode'] ?? null) !== 'terbuka') {
            $this->kosongkan();
            session([self::KUNCI => ['mode' => 'terbuka', 'profil_pengepul_id' => null, 'item' => []]]);
        }
    }

    public function ada(): bool
    {
        return session()->has(self::KUNCI);
    }

    /** @return array{mode: string, profil_pengepul_id: ?int, item: array<int, array>} */
    public function ambil(): array
    {
        return session(self::KUNCI, []);
    }

    public function pengepul(): ?ProfilPengepul
    {
        $id = $this->ambil()['profil_pengepul_id'] ?? null;

        return $id ? ProfilPengepul::with(['user.wilayah', 'harga.kategori.induk'])->find($id) : null;
    }

    public function terbuka(): bool
    {
        return ($this->ambil()['mode'] ?? null) === 'terbuka';
    }

    /** Kategori yang boleh ditambahkan ke keranjang saat ini. */
    public function kategoriTersedia(): Collection
    {
        if ($pengepul = $this->pengepul()) {
            return $pengepul->harga
                ->where('sedang_menerima', true)
                ->filter(fn ($h) => ! $h->kategori->limbah_b3 || $pengepul->izin_b3)
                ->map(fn ($h) => $h->kategori->setAttribute('harga_tampil', (float) $h->harga_per_satuan)
                    ->setAttribute('min_berat', (float) $h->min_berat))
                ->sortBy(fn ($k) => [$k->induk?->urutan, $k->urutan])
                ->values();
        }

        return KategoriSampah::turunan()->aktif()->with('induk')->get()
            ->map(fn ($k) => $k->setAttribute('harga_tampil', $this->permintaan->hargaPerkiraan($k))
                ->setAttribute('min_berat', 0))
            ->sortBy(fn ($k) => [$k->induk?->urutan, $k->urutan])
            ->values();
    }

    public function tambah(int $kategoriId, float $berat, ?UploadedFile $foto, ?string $catatan): void
    {
        $data = $this->ambil();
        $kategori = $this->kategoriTersedia()->firstWhere('id', $kategoriId);

        if (! $kategori) {
            throw new AksiTidakValid('Kategori ini tidak tersedia untuk pengajuan Anda.');
        }

        // Kategori yang sama digabung, agar satu permintaan tidak punya dua baris kardus.
        foreach ($data['item'] as $i => $baris) {
            if ((int) $baris['kategori_sampah_id'] === $kategoriId) {
                $beratBaru = round((float) $baris['estimasi_berat'] + $berat, 2);
                $this->pastikanMinimum($kategori, $beratBaru);
                $data['item'][$i]['estimasi_berat'] = $beratBaru;
                session([self::KUNCI => $data]);

                return;
            }
        }

        $this->pastikanMinimum($kategori, $berat);

        $data['item'][] = [
            'kategori_sampah_id' => $kategoriId,
            'estimasi_berat' => round($berat, 2),
            // Foto barang bersifat pribadi, jadi disimpan di disk lokal, bukan publik.
            'foto' => $foto?->store('foto-barang', 'local'),
            'catatan' => $catatan,
        ];

        session([self::KUNCI => $data]);
    }

    public function hapus(int $indeks): void
    {
        $data = $this->ambil();

        if (isset($data['item'][$indeks])) {
            if ($foto = $data['item'][$indeks]['foto'] ?? null) {
                Storage::disk('local')->delete($foto);
            }

            unset($data['item'][$indeks]);
            $data['item'] = array_values($data['item']);
            session([self::KUNCI => $data]);
        }
    }

    /** Baris keranjang lengkap dengan model kategori dan estimasi subtotal. */
    public function rincian(): Collection
    {
        $tersedia = $this->kategoriTersedia()->keyBy('id');

        return collect($this->ambil()['item'] ?? [])->map(function ($baris, $i) use ($tersedia) {
            $kategori = $tersedia[$baris['kategori_sampah_id']] ?? KategoriSampah::find($baris['kategori_sampah_id']);
            $harga = (float) ($kategori?->harga_tampil ?? 0);

            return (object) [
                'indeks' => $i,
                'kategori' => $kategori,
                'berat' => (float) $baris['estimasi_berat'],
                'harga' => $harga,
                'subtotal' => round((float) $baris['estimasi_berat'] * $harga, 2),
                'foto' => $baris['foto'] ?? null,
                'catatan' => $baris['catatan'] ?? null,
            ];
        });
    }

    public function kosongkan(bool $hapusFoto = true): void
    {
        if ($hapusFoto) {
            foreach ($this->ambil()['item'] ?? [] as $baris) {
                if (! empty($baris['foto'])) {
                    Storage::disk('local')->delete($baris['foto']);
                }
            }
        }

        session()->forget(self::KUNCI);
    }

    private function pastikanMinimum(KategoriSampah $kategori, float $berat): void
    {
        $min = (float) ($kategori->min_berat ?? 0);

        if ($min > 0 && $berat < $min) {
            throw new AksiTidakValid("{$kategori->nama} diterima minimal ".berat($min).'.');
        }
    }
}
