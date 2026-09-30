<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengepul dengan saldo di bawah ambang tidak boleh menerima permintaan baru.
 * Inilah yang mendorong pengisian saldo, sumber pendapatan platform.
 */
class PastikanSaldoCukup
{
    public function handle(Request $request, Closure $next): Response
    {
        $profil = $request->user()?->profilPengepul;

        if ($profil && ! $profil->saldoCukup()) {
            return redirect()
                ->route('pengepul.dompet.index')
                ->with('peringatan', 'Saldo Anda di bawah '.rupiah(pengaturan('saldo_minimum', 10000))
                    .'. Isi saldo dulu untuk bisa menerima permintaan baru.');
        }

        return $next($request);
    }
}
