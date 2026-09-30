<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Enums\StatusVerifikasi;
use App\Models\HargaPengepul;
use App\Models\KategoriSampah;
use App\Models\ProfilPengepul;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahan = Wilayah::kelurahan()->get()->keyBy('nama');
        $kategori = KategoriSampah::turunan()->get();

        User::create([
            'name' => 'Admin RongsokKu',
            'email' => 'admin@rongsokku.test',
            'password' => Hash::make('password'),
            'peran' => PeranPengguna::Admin,
            'telepon' => '081234567890',
            'email_verified_at' => now(),
        ]);

        // --- Warga ---
        $warga = [
            ['Andhika Eka Pratama', 'andhika@warga.test', 'Nagasari', '081311112222'],
            ['Siti Aminah', 'siti@warga.test', 'Adiarsa Timur', '081322223333'],
            ['Budi Santoso', 'budi@warga.test', 'Klari', '081333334444'],
            ['Dewi Lestari', 'dewi@warga.test', 'Sukaluyu', '081344445555'],
            ['Rahmat Hidayat', 'rahmat@warga.test', 'Tanjungpura', '081355556666'],
            ['Nur Aisyah', 'nur@warga.test', 'Palumbonsari', '081366667777'],
            ['Joko Widodo', 'joko@warga.test', 'Nagasari', '081377778888'],
            ['Maya Sari', 'maya@warga.test', 'Cikampek Kota', '081388889999'],
        ];

        foreach ($warga as [$nama, $email, $namaKelurahan, $telepon]) {
            $w = $kelurahan[$namaKelurahan];

            User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make('password'),
                'peran' => PeranPengguna::Warga,
                'telepon' => $telepon,
                'wilayah_id' => $w->id,
                'alamat_detail' => 'Jl. '.Str::before($nama, ' ').' No. '.rand(1, 90).', RT 0'.rand(1, 9).'/RW 0'.rand(1, 5),
                // Digeser sedikit acak agar titik tiap warga tidak bertumpuk.
                'latitude' => $w->latitude + (rand(-40, 40) / 10000),
                'longitude' => $w->longitude + (rand(-40, 40) / 10000),
                'email_verified_at' => now(),
            ]);
        }

        // --- Pengepul ---
        // Kolom "gaya" menentukan kecenderungan harga terhadap rentang acuan,
        // supaya perbandingan harga pada demo terasa nyata.
        $pengepul = [
            ['Pengepul Jaya', 'jaya@pengepul.test', 'Nagasari', '081211112222', 'tinggi', true, 4.8, 32],
            ['Rongsok Berkah', 'berkah@pengepul.test', 'Adiarsa Timur', '081222223333', 'rendah', false, 4.2, 18],
            ['Barokah Jaya Mandiri', 'barokah@pengepul.test', 'Klari', '081233334444', 'sedang', false, 4.6, 24],
            ['Lapak Hijau Karawang', 'hijau@pengepul.test', 'Sukaluyu', '081244445555', 'tinggi', true, 4.9, 41],
            ['Sumber Rezeki Rongsok', 'rezeki@pengepul.test', 'Tanjungpura', '081255556666', 'sedang', false, 4.4, 15],
            ['Mitra Daur Cikampek', 'mitra@pengepul.test', 'Cikampek Kota', '081266667777', 'sedang', false, 4.1, 9],
        ];

        foreach ($pengepul as [$namaUsaha, $email, $namaKelurahan, $telepon, $gaya, $izinB3, $rating, $jumlahUlasan]) {
            $w = $kelurahan[$namaKelurahan];

            $user = User::create([
                'name' => Str::of($namaUsaha)->before(' ')->append(' Pemilik'),
                'email' => $email,
                'password' => Hash::make('password'),
                'peran' => PeranPengguna::Pengepul,
                'telepon' => $telepon,
                'wilayah_id' => $w->id,
                'alamat_detail' => 'Jl. Raya '.$namaKelurahan.' No. '.rand(10, 200),
                'latitude' => $w->latitude + (rand(-30, 30) / 10000),
                'longitude' => $w->longitude + (rand(-30, 30) / 10000),
                'email_verified_at' => now(),
            ]);

            $profil = ProfilPengepul::create([
                'user_id' => $user->id,
                'nama_usaha' => $namaUsaha,
                'slug' => Str::slug($namaUsaha),
                'deskripsi' => "Melayani penjemputan rongsok di sekitar $namaKelurahan dan wilayah terdekat. "
                    .'Timbangan digital, harga sesuai kesepakatan awal.',
                'status_verifikasi' => StatusVerifikasi::Terverifikasi,
                'diverifikasi_pada' => now()->subDays(rand(30, 120)),
                'izin_b3' => $izinB3,
                'jam_buka' => '07:00:00',
                'jam_tutup' => rand(0, 1) ? '17:00:00' : '18:00:00',
                'radius_layanan_km' => rand(5, 15),
                'saldo' => rand(50, 500) * 1000,
                'rating_rata' => $rating,
                'jumlah_ulasan' => $jumlahUlasan,
            ]);

            foreach ($kategori as $k) {
                // Pengepul tanpa izin B3 tidak boleh memasang harga limbah B3.
                if ($k->limbah_b3 && ! $izinB3) {
                    continue;
                }

                $min = (float) $k->harga_acuan_min;
                $max = (float) $k->harga_acuan_max;
                $tengah = ($min + $max) / 2;

                $harga = match ($gaya) {
                    'tinggi' => $tengah + ($max - $tengah) * (rand(40, 95) / 100),
                    'rendah' => $min * (rand(78, 96) / 100),
                    default => $tengah * (rand(92, 108) / 100),
                };

                HargaPengepul::create([
                    'profil_pengepul_id' => $profil->id,
                    'kategori_sampah_id' => $k->id,
                    'harga_per_satuan' => round($harga / 50) * 50,
                    'min_berat' => [0, 0, 1, 2, 5][rand(0, 4)],
                    'sedang_menerima' => rand(1, 20) > 1,
                ]);
            }
        }
    }
}
