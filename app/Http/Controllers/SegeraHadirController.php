<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Penanda modul yang kerangkanya sudah disiapkan namun isinya
 * dikerjakan pada fase berikutnya oleh anggota tim terkait.
 */
class SegeraHadirController extends Controller
{
    public function __invoke(Request $request, string $judul = 'Modul', string $penanggungJawab = 'Tim'): View
    {
        return view('segera-hadir', [
            'judul' => $judul,
            'penanggungJawab' => $penanggungJawab,
        ]);
    }
}
