<?php

use App\Exceptions\AksiTidakValid;
use App\Http\Middleware\PastikanPengepulTerverifikasi;
use App\Http\Middleware\PastikanPeran;
use App\Http\Middleware\PastikanSaldoCukup;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => PastikanPeran::class,
            'pengepul.terverifikasi' => PastikanPengepulTerverifikasi::class,
            'saldo.cukup' => PastikanSaldoCukup::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Pelanggaran aturan bisnis dari service dikembalikan ke halaman
        // sebelumnya sebagai pesan galat, bukan halaman error 500.
        $exceptions->render(function (AksiTidakValid $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        });
    })->create();
