<?php

namespace App\Console\Commands;

use App\Models\ProfilPengepul;
use App\Services\MetrikPengepulService;
use Illuminate\Console\Command;

class SegarkanMetrikPengepul extends Command
{
    protected $signature = 'rongsokku:segarkan-metrik';

    protected $description = 'Hitung ulang rating, kepatuhan harga, dan metrik objektif seluruh pengepul';

    public function handle(MetrikPengepulService $metrik): int
    {
        $jumlah = 0;

        ProfilPengepul::chunkById(100, function ($daftar) use ($metrik, &$jumlah) {
            foreach ($daftar as $pengepul) {
                $metrik->segarkan($pengepul);
                $jumlah++;
            }
        });

        $this->info("Metrik {$jumlah} pengepul diperbarui.");

        return self::SUCCESS;
    }
}
