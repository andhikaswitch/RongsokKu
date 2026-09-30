<?php

namespace App\Support;

/**
 * Perhitungan jarak dua titik koordinat di permukaan bumi.
 * Dipakai untuk fitur "pengepul terdekat" tanpa layanan peta berbayar.
 */
class Haversine
{
    /** Jari-jari rata-rata bumi dalam kilometer. */
    public const RADIUS_BUMI_KM = 6371;

    public static function jarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round(self::RADIUS_BUMI_KM * 2 * asin(min(1.0, sqrt($a))), 2);
    }

    /**
     * Potongan SQL untuk menghitung jarak langsung di query,
     * sehingga pengurutan "terdekat" bisa dikerjakan database.
     */
    public static function sql(string $kolomLat, string $kolomLng, float $lat, float $lng): string
    {
        $r = self::RADIUS_BUMI_KM;

        return "($r * 2 * ASIN(SQRT(
            POWER(SIN(RADIANS($lat - $kolomLat) / 2), 2) +
            COS(RADIANS($kolomLat)) * COS(RADIANS($lat)) *
            POWER(SIN(RADIANS($lng - $kolomLng) / 2), 2)
        )))";
    }
}
