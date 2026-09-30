<?php

namespace Database\Seeders;

use App\Models\KategoriSampah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KategoriSampahSeeder extends Seeder
{
    public function run(): void
    {
        // Enam golongan induk mengikuti klasifikasi umum rongsok di Indonesia.
        // Turunannya dipakai untuk penetapan harga yang lebih presisi.
        $data = [
            [
                'nama' => 'Kertas', 'ikon' => '📄',
                'deskripsi' => 'Kertas dan turunannya yang masih kering dan bersih.',
                'co2' => 0.94,
                'anak' => [
                    ['Kardus', 1800, 2500, 'Kardus bekas kemasan, kondisi kering.'],
                    ['Kertas HVS', 2200, 3000, 'Kertas putih bekas fotokopi atau print.'],
                    ['Koran', 1500, 2200, 'Koran dan majalah bekas.'],
                    ['Buku & Arsip', 1400, 2000, 'Buku tulis dan arsip campuran.'],
                ],
            ],
            [
                'nama' => 'Plastik', 'ikon' => '🥤',
                'deskripsi' => 'Kemasan dan perabot berbahan plastik.',
                'co2' => 1.53,
                'anak' => [
                    ['Botol PET Bening', 3000, 4200, 'Botol air mineral bening, tanpa tutup dan label.'],
                    ['Gelas Plastik', 2500, 3600, 'Gelas air mineral, sudah dibersihkan.'],
                    ['Ember & Perabot', 2000, 2800, 'Ember, baskom, kursi plastik.'],
                    ['Plastik Campur', 800, 1500, 'Kantong kresek dan plastik kemasan campuran.'],
                ],
            ],
            [
                'nama' => 'Logam', 'ikon' => '🔩',
                'deskripsi' => 'Besi dan logam non-besi. Harga mengikuti pasar komoditas.',
                'co2' => 2.10,
                'anak' => [
                    ['Besi Tua', 3500, 5500, 'Besi rongsok, pagar, rangka.'],
                    ['Alumunium', 14000, 19000, 'Panci, velg, kusen alumunium.'],
                    ['Tembaga', 65000, 95000, 'Kabel tembaga, lilitan dinamo.'],
                    ['Kaleng', 2000, 3200, 'Kaleng minuman dan makanan.'],
                ],
            ],
            [
                'nama' => 'Kaca', 'ikon' => '🍾',
                'deskripsi' => 'Botol dan pecahan kaca. Harap dibungkus agar aman.',
                'co2' => 0.31,
                'anak' => [
                    ['Botol Kaca Utuh', 500, 1200, 'Botol kecap, sirup, bir dalam kondisi utuh.'],
                    ['Beling', 200, 600, 'Pecahan kaca, wajib dibungkus rapat.'],
                ],
            ],
            [
                'nama' => 'Elektronik', 'ikon' => '📺',
                'deskripsi' => 'Perangkat elektronik bekas. Sebagian tergolong limbah B3.',
                'co2' => 3.20,
                'anak' => [
                    ['Kabel & Charger', 8000, 14000, 'Kabel listrik, charger, adaptor.'],
                    ['HP & Gadget Rusak', 5000, 25000, 'Ponsel dan tablet yang sudah tidak berfungsi.'],
                    ['Peralatan Rumah Tangga', 3000, 8000, 'Kipas angin, rice cooker, setrika.'],
                    ['Baterai Bekas', 1000, 3000, 'Baterai kering, aki kecil, powerbank.', true,
                        'Mengandung logam berat (timbal, kadmium, merkuri) yang mencemari tanah dan air tanah. Jangan dibuang bersama sampah rumah tangga dan jangan ditusuk atau dibakar. Hanya pengepul berizin B3 yang boleh menerima.'],
                ],
            ],
            [
                'nama' => 'Kain', 'ikon' => '👕',
                'deskripsi' => 'Tekstil bekas yang masih kering dan tidak berjamur.',
                'co2' => 1.10,
                'anak' => [
                    ['Pakaian Bekas', 1500, 3000, 'Pakaian layak pakai maupun tidak.'],
                    ['Kain Perca', 800, 1600, 'Sisa kain dan potongan konveksi.'],
                    ['Karung', 1000, 2000, 'Karung beras dan karung plastik anyam.'],
                ],
            ],
        ];

        $urutan = 0;

        foreach ($data as $induk) {
            $parent = KategoriSampah::create([
                'nama' => $induk['nama'],
                'slug' => Str::slug($induk['nama']),
                'ikon' => $induk['ikon'],
                'deskripsi' => $induk['deskripsi'],
                'faktor_co2_per_kg' => $induk['co2'],
                'urutan' => $urutan += 10,
            ]);

            $urutanAnak = 0;

            foreach ($induk['anak'] as $anak) {
                [$nama, $min, $max, $keterangan] = $anak;

                KategoriSampah::create([
                    'induk_id' => $parent->id,
                    'nama' => $nama,
                    'slug' => Str::slug($nama),
                    'ikon' => $induk['ikon'],
                    'deskripsi' => $keterangan,
                    'harga_acuan_min' => $min,
                    'harga_acuan_max' => $max,
                    'faktor_co2_per_kg' => $induk['co2'],
                    'limbah_b3' => $anak[4] ?? false,
                    'peringatan_b3' => $anak[5] ?? null,
                    'urutan' => $urutanAnak += 10,
                ]);
            }
        }
    }
}
