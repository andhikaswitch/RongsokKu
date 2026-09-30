<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanPengepulTerverifikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        $profil = $request->user()?->profilPengepul;

        if (! $profil) {
            return redirect()->route('pengepul.verifikasi.form');
        }

        if (! $profil->terverifikasi()) {
            return redirect()->route('pengepul.verifikasi.status');
        }

        return $next($request);
    }
}
