<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Enums\StatusPermintaan;
use App\Enums\StatusTopup;
use App\Enums\StatusVerifikasi;
use App\Models\PermintaanJemput;
use App\Models\PermintaanTopup;
use App\Models\ProfilPengepul;
use App\Models\Sengketa;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi antrean admin dengan satu contoh masing-masing, supaya fitur
 * verifikasi, top-up, dan sengketa bisa langsung didemokan saat sidang.
 */
class AntreanDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->pengepulMenungguVerifikasi();
        $this->topupMenunggu();
        $this->sengketaTerbuka();
    }

    private function pengepulMenungguVerifikasi(): void
    {
        $wilayah = Wilayah::kelurahan()->where('nama', 'Plawad')->first() ?? Wilayah::kelurahan()->first();

        $user = User::create([
            'name' => 'Ujang Maju',
            'email' => 'maju@pengepul.test',
            'password' => Hash::make('password'),
            'peran' => PeranPengguna::Pengepul,
            'telepon' => '081299998888',
            'wilayah_id' => $wilayah->id,
            'alamat_detail' => 'Jl. Plawad Raya No. 7',
            'latitude' => $wilayah->latitude,
            'longitude' => $wilayah->longitude,
            'email_verified_at' => now(),
        ]);

        ProfilPengepul::create([
            'user_id' => $user->id,
            'nama_usaha' => 'Rongsok Maju Bersama',
            'slug' => 'rongsok-maju-bersama',
            'deskripsi' => 'Lapak baru di Karawang Timur, menerima kardus dan logam.',
            'foto_ktp' => $this->gambarContoh('ktp/contoh-ktp.svg', 'CONTOH KTP', 'local'),
            'foto_lapak' => $this->gambarContoh('lapak/contoh-lapak.svg', 'FOTO LAPAK', 'public'),
            'status_verifikasi' => StatusVerifikasi::Menunggu,
        ]);
    }

    private function topupMenunggu(): void
    {
        $pengepul = ProfilPengepul::where('slug', 'pengepul-jaya')->first();

        if (! $pengepul) {
            return;
        }

        PermintaanTopup::create([
            'kode' => 'TOP-'.str_pad((string) ((int) PermintaanTopup::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'profil_pengepul_id' => $pengepul->id,
            'jumlah' => 200000,
            'bank_pengirim' => 'Bank BRI',
            'nama_pengirim' => $pengepul->user->name,
            'bukti_transfer' => $this->gambarContoh('bukti-transfer/contoh-bukti.svg', 'BUKTI TRANSFER Rp 200.000', 'local'),
            'status' => StatusTopup::Menunggu,
        ]);
    }

    private function sengketaTerbuka(): void
    {
        $p = PermintaanJemput::where('status', StatusPermintaan::MenungguKonfirmasi)->with('item')->first();

        if (! $p) {
            return;
        }

        // Pengepul menurunkan harga di lokasi, lalu warga mengajukan keberatan.
        $total = 0;
        foreach ($p->item as $item) {
            $harga = round((float) $item->harga_estimasi_per_satuan * 0.75 / 50) * 50;
            $sub = round((float) $item->berat_final * $harga, 2);
            $item->update(['harga_final_per_satuan' => $harga, 'subtotal_final' => $sub]);
            $total += $sub;
        }

        $p->forceFill([
            'status' => StatusPermintaan::Sengketa,
            'total_final' => $total,
            'catatan_pengepul' => 'Barang agak basah, harga saya sesuaikan.',
        ])->save();

        Sengketa::create([
            'kode' => 'SKT-00001',
            'permintaan_jemput_id' => $p->id,
            'dilaporkan_oleh' => $p->warga_id,
            'alasan' => 'Harga diturunkan sepihak di lokasi',
            'deskripsi' => 'Di aplikasi harga yang dikunci sudah jelas, tapi saat di rumah pengepul hanya mau membayar sekitar 75% dari harga itu. Barang saya kering.',
            'status' => 'menunggu',
        ]);
    }

    /** Gambar SVG sederhana sebagai pengganti foto asli pada data demo. */
    private function gambarContoh(string $path, string $teks, string $disk): string
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="400" viewBox="0 0 640 400">
  <rect width="640" height="400" rx="24" fill="#ecfdf5"/>
  <rect x="24" y="24" width="592" height="352" rx="16" fill="none" stroke="#10b981" stroke-width="4" stroke-dasharray="12 10"/>
  <text x="320" y="190" text-anchor="middle" font-family="sans-serif" font-size="34" font-weight="700" fill="#047857">{$teks}</text>
  <text x="320" y="240" text-anchor="middle" font-family="sans-serif" font-size="20" fill="#059669">Data demo RongsokKu</text>
</svg>
SVG;

        Storage::disk($disk)->put($path, $svg);

        return $path;
    }
}
