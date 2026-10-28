<?php

namespace App\Support;

/**
 * Tautan Google Maps lewat Maps URLs resmi: gratis, tanpa API key, dan
 * di HP langsung terbuka di aplikasi Google Maps bila terpasang.
 */
class GoogleMaps
{
    /** Teks koordinat siap salin, format yang sama dengan Google Maps. */
    public static function koordinat(?float $lat, ?float $lng): ?string
    {
        if (! $lat || ! $lng) {
            return null;
        }

        return number_format($lat, 6, '.', '').', '.number_format($lng, 6, '.', '');
    }

    /** Membuka titik lokasi di Google Maps. */
    public static function lokasi(?float $lat, ?float $lng): ?string
    {
        $titik = self::koordinat($lat, $lng);

        return $titik ? 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => str_replace(' ', '', $titik)]) : null;
    }

    /** Petunjuk arah dari lokasi pengguna saat ini ke titik tujuan. */
    public static function rute(?float $lat, ?float $lng): ?string
    {
        $titik = self::koordinat($lat, $lng);

        return $titik ? 'https://www.google.com/maps/dir/?'.http_build_query(['api' => 1, 'destination' => str_replace(' ', '', $titik)]) : null;
    }
}
