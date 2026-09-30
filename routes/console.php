<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Jadwal Tugas
|--------------------------------------------------------------------------
| Jalankan penjadwal dengan: php artisan schedule:work
| (atau pasang cron: * * * * * php artisan schedule:run)
*/

// Indeks dihitung setelah tengah malam agar transaksi sehari penuh ikut terhitung.
Schedule::command('rongsokku:hitung-indeks')->dailyAt('00:05');
Schedule::command('rongsokku:segarkan-metrik')->dailyAt('00:15');
