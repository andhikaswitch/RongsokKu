<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $provinsi = Wilayah::create([
            'nama' => 'Jawa Barat',
            'tingkat' => 'provinsi',
            'latitude' => -6.9147444,
            'longitude' => 107.6098111,
        ]);

        $kabupaten = Wilayah::create([
            'induk_id' => $provinsi->id,
            'nama' => 'Kabupaten Karawang',
            'tingkat' => 'kabupaten',
            'latitude' => -6.3227,
            'longitude' => 107.3376,
        ]);

        // Koordinat kelurahan dipakai sebagai titik acuan perhitungan jarak
        // Haversine, sehingga warga tidak perlu mengisi lat/lng sendiri.
        $data = [
            'Karawang Barat' => [
                ['Nagasari', -6.3059, 107.2951],
                ['Karawang Kulon', -6.3131, 107.2909],
                ['Tanjungpura', -6.3238, 107.2846],
                ['Adiarsa Barat', -6.3212, 107.3018],
                ['Tanjungmekar', -6.2967, 107.2893],
            ],
            'Karawang Timur' => [
                ['Adiarsa Timur', -6.3188, 107.3167],
                ['Palumbonsari', -6.3094, 107.3289],
                ['Warungbambu', -6.3011, 107.3401],
                ['Plawad', -6.3272, 107.3245],
            ],
            'Klari' => [
                ['Duren', -6.3372, 107.3861],
                ['Klari', -6.3419, 107.3702],
                ['Anggadita', -6.3287, 107.3944],
            ],
            'Telukjambe Timur' => [
                ['Sukaluyu', -6.3405, 107.2807],
                ['Sirnabaya', -6.3521, 107.2913],
                ['Pinayungan', -6.3318, 107.2736],
            ],
            'Cikampek' => [
                ['Cikampek Kota', -6.4092, 107.4569],
                ['Dawuan Tengah', -6.4183, 107.4471],
            ],
        ];

        foreach ($data as $namaKecamatan => $kelurahan) {
            $latRata = array_sum(array_column($kelurahan, 1)) / count($kelurahan);
            $lngRata = array_sum(array_column($kelurahan, 2)) / count($kelurahan);

            $kec = Wilayah::create([
                'induk_id' => $kabupaten->id,
                'nama' => $namaKecamatan,
                'tingkat' => 'kecamatan',
                'latitude' => round($latRata, 7),
                'longitude' => round($lngRata, 7),
            ]);

            foreach ($kelurahan as [$nama, $lat, $lng]) {
                Wilayah::create([
                    'induk_id' => $kec->id,
                    'nama' => $nama,
                    'tingkat' => 'kelurahan',
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            }
        }
    }
}
