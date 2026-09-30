<?php

namespace Database\Seeders;

use App\Enums\TipeSumberHarga;
use App\Models\KategoriSampah;
use App\Models\SumberHargaPasar;
use App\Models\User;
use Illuminate\Database\Seeder;

class SumberHargaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('peran', 'admin')->first();

        // Harga acuan resmi beserta jejak sumbernya. Tidak ada lembaga tunggal
        // yang menerbitkan harga rongsok nasional secara berkala, sehingga
        // acuan disusun dari sumber lokal yang bisa ditelusuri kembali.
        $sumber = [
            'kertas' => ['Bank Sampah Induk Karawang', TipeSumberHarga::BankSampah],
            'plastik' => ['Bank Sampah Induk Karawang', TipeSumberHarga::BankSampah],
            'logam' => ['Rata-rata 3 Pabrik Daur Ulang Karawang', TipeSumberHarga::Pabrik],
            'kaca' => ['Survei Lapangan Tim RongsokKu', TipeSumberHarga::SurveiInternal],
            'elektronik' => ['Survei Lapangan Tim RongsokKu', TipeSumberHarga::SurveiInternal],
            'kain' => ['Survei Lapangan Tim RongsokKu', TipeSumberHarga::SurveiInternal],
        ];

        foreach (KategoriSampah::turunan()->with('induk')->get() as $kategori) {
            $slugInduk = $kategori->induk?->slug;

            if (! isset($sumber[$slugInduk]) || ! $kategori->harga_acuan_min) {
                continue;
            }

            [$nama, $tipe] = $sumber[$slugInduk];

            SumberHargaPasar::create([
                'kategori_sampah_id' => $kategori->id,
                'harga_min' => $kategori->harga_acuan_min,
                'harga_max' => $kategori->harga_acuan_max,
                'sumber_nama' => $nama,
                'sumber_tipe' => $tipe,
                'berlaku_mulai' => now()->subDays(14)->toDateString(),
                'dicatat_oleh' => $admin?->id,
            ]);
        }
    }
}
