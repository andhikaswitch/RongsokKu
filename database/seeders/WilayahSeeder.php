<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Seluruh 30 kecamatan dan 309 desa/kelurahan Kabupaten Karawang.
     *
     * Memakai updateOrCreate agar aman dijalankan ulang pada database yang
     * sudah berisi data: wilayah lama tetap memakai ID yang sama (sehingga
     * pengguna tidak kehilangan kelurahannya) dan wilayah yang belum ada ditambahkan.
     */
    public function run(): void
    {
        $provinsi = $this->simpan(null, 'Jawa Barat', 'provinsi', -6.9147444, 107.6098111);
        $kabupaten = $this->simpan($provinsi->id, 'Kabupaten Karawang', 'kabupaten', -6.3227, 107.3376);

        // Koordinat desa dipakai sebagai titik awal perhitungan jarak Haversine;
        // pengguna dapat memperhalus titiknya sendiri lewat halaman "Atur titik di peta".
        $data = require database_path('data/wilayah_karawang.php');

        foreach ($data as [$namaKecamatan, $desa]) {
            $lat = array_sum(array_column($desa, 2)) / count($desa);
            $lng = array_sum(array_column($desa, 3)) / count($desa);
            $kecamatan = $this->simpan($kabupaten->id, $namaKecamatan, 'kecamatan', round($lat, 7), round($lng, 7));

            foreach ($desa as [, $nama, $latDesa, $lngDesa]) {
                $this->simpan($kecamatan->id, $nama, 'kelurahan', $latDesa, $lngDesa);
            }
        }
    }

    private function simpan(?int $induk, string $nama, string $tingkat, float $lat, float $lng): Wilayah
    {
        return Wilayah::updateOrCreate(
            ['induk_id' => $induk, 'nama' => $nama, 'tingkat' => $tingkat],
            ['latitude' => $lat, 'longitude' => $lng],
        );
    }
}
