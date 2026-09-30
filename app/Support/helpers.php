<?php

use App\Models\PengaturanPlatform;
use App\Support\Format;

if (! function_exists('pengaturan')) {
    /** Ambil satu nilai pengaturan platform. */
    function pengaturan(string $kunci, mixed $bawaan = null): mixed
    {
        return PengaturanPlatform::ambil($kunci, $bawaan);
    }
}

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $nilai, bool $ringkas = false): string
    {
        return Format::rupiah($nilai, $ringkas);
    }
}

if (! function_exists('berat')) {
    function berat(float|int|string|null $nilai, string $satuan = 'kg'): string
    {
        return Format::berat($nilai, $satuan);
    }
}

if (! function_exists('jarak')) {
    function jarak(?float $km): string
    {
        return Format::jarak($km);
    }
}

if (! function_exists('tanggal_id')) {
    function tanggal_id(?DateTimeInterface $tanggal): string
    {
        return Format::tanggal($tanggal);
    }
}
