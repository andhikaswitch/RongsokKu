<?php

namespace App\Support;

class Format
{
    /** Contoh keluaran: "Rp 16.600". */
    public static function rupiah(float|int|string|null $nilai, bool $ringkas = false): string
    {
        $nilai = (float) ($nilai ?? 0);

        if ($ringkas && abs($nilai) >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($nilai / 1_000_000, 1, ',', '.'), '0'), ',').' jt';
        }

        if ($ringkas && abs($nilai) >= 1_000) {
            return 'Rp '.rtrim(rtrim(number_format($nilai / 1_000, 1, ',', '.'), '0'), ',').' rb';
        }

        return 'Rp '.number_format($nilai, 0, ',', '.');
    }

    /** Contoh keluaran: "8,3 kg". */
    public static function berat(float|int|string|null $nilai, string $satuan = 'kg'): string
    {
        $nilai = (float) ($nilai ?? 0);
        $angka = fmod($nilai, 1) === 0.0
            ? number_format($nilai, 0, ',', '.')
            : rtrim(rtrim(number_format($nilai, 2, ',', '.'), '0'), ',');

        return "$angka $satuan";
    }

    /** Contoh keluaran: "1,2 km" atau "850 m". */
    public static function jarak(?float $km): string
    {
        if ($km === null) {
            return '-';
        }

        if ($km < 1) {
            return round($km * 1000).' m';
        }

        return number_format($km, 1, ',', '.').' km';
    }

    /** Contoh keluaran: "+3,2%" dengan tanda eksplisit. */
    public static function persen(?float $nilai, bool $bertanda = true): string
    {
        if ($nilai === null) {
            return '-';
        }

        $tanda = $bertanda && $nilai > 0 ? '+' : '';

        return $tanda.number_format($nilai, 1, ',', '.').'%';
    }

    /** Contoh keluaran: "23 Sep 2026". */
    public static function tanggal(?\DateTimeInterface $tanggal): string
    {
        if (! $tanggal) {
            return '-';
        }

        $bulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return $tanggal->format('j').' '.$bulan[(int) $tanggal->format('n')].' '.$tanggal->format('Y');
    }

    /** Nomor telepon Indonesia ke format wa.me (62xxx). */
    public static function nomorWa(?string $telepon): ?string
    {
        if (! $telepon) {
            return null;
        }

        $bersih = preg_replace('/\D/', '', $telepon);

        if (str_starts_with($bersih, '0')) {
            return '62'.substr($bersih, 1);
        }

        return str_starts_with($bersih, '62') ? $bersih : '62'.$bersih;
    }
}
