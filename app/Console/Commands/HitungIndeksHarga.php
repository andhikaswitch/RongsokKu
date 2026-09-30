<?php

namespace App\Console\Commands;

use App\Services\IndeksHargaService;
use Illuminate\Console\Command;

class HitungIndeksHarga extends Command
{
    protected $signature = 'rongsokku:hitung-indeks
                            {--hari=0 : Hitung juga mundur sekian hari ke belakang, untuk mengisi riwayat grafik}';

    protected $description = 'Hitung Indeks Harga RongsokKu dari transaksi yang sudah selesai';

    public function handle(IndeksHargaService $indeks): int
    {
        $mundur = max(0, (int) $this->option('hari'));

        for ($h = $mundur; $h >= 0; $h--) {
            $tanggal = now()->subDays($h);
            $jumlah = $indeks->hitungSemua($tanggal);
            $this->line(sprintf('  %s  %d kategori', $tanggal->toDateString(), $jumlah));
        }

        $this->info('Indeks harga selesai dihitung.');

        return self::SUCCESS;
    }
}
