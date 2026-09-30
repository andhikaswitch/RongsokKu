<?php

namespace Database\Seeders;

use App\Models\Lencana;
use App\Models\ProfilPengepul;
use App\Models\User;
use App\Services\IndeksHargaService;
use App\Services\MetrikPengepulService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            KategoriSampahSeeder::class,
            PengaturanSeeder::class,
            PenggunaSeeder::class,
            SumberHargaSeeder::class,
            TransaksiDemoSeeder::class,
        ]);

        $this->command->info('Menghitung Indeks Harga dari transaksi demo...');
        $indeks = app(IndeksHargaService::class);

        // Indeks dihitung mundur 30 hari agar grafik tren punya riwayat.
        for ($hari = 30; $hari >= 0; $hari--) {
            $indeks->hitungSemua(now()->subDays($hari));
        }

        $this->command->info('Menyegarkan metrik objektif pengepul...');
        $metrik = app(MetrikPengepulService::class);

        foreach (ProfilPengepul::all() as $pengepul) {
            $metrik->segarkan($pengepul);
        }

        $this->command->info('Memberikan lencana kepada warga...');
        $this->berikanLencana();

        $this->command->newLine();
        $this->command->info('Akun demo (kata sandi semua: password)');
        $this->command->table(
            ['Peran', 'Email'],
            [
                ['Admin', 'admin@rongsokku.test'],
                ['Warga', 'andhika@warga.test'],
                ['Pengepul', 'jaya@pengepul.test'],
            ]
        );
    }

    private function berikanLencana(): void
    {
        $lencana = Lencana::orderBy('syarat_berat_kg')->get();

        foreach (User::where('peran', 'warga')->get() as $warga) {
            $berat = $warga->totalBeratDidaurUlang();

            foreach ($lencana as $l) {
                if ($berat >= (float) $l->syarat_berat_kg) {
                    $warga->lencana()->syncWithoutDetaching([
                        $l->id => ['diraih_pada' => now()->subDays(rand(1, 30))],
                    ]);
                }
            }
        }
    }
}
