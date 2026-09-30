<?php

namespace App\Support;

/**
 * Menyusun URL peta OpenStreetMap.
 *
 * Memakai embed resmi OSM lewat iframe: gratis, tanpa API key, dan tanpa
 * satu baris JavaScript pun sehingga tetap memenuhi batasan proyek.
 */
class OsmEmbed
{
    /** URL iframe yang berpusat pada satu titik beserta penandanya. */
    public static function titik(?float $lat, ?float $lng, float $rentang = 0.008): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        $bbox = implode(',', [
            $lng - $rentang,
            $lat - $rentang / 2,
            $lng + $rentang,
            $lat + $rentang / 2,
        ]);

        return 'https://www.openstreetmap.org/export/embed.html?'.http_build_query([
            'bbox' => $bbox,
            'layer' => 'mapnik',
            'marker' => "$lat,$lng",
        ]);
    }

    /** Tautan untuk membuka peta ukuran penuh di tab baru. */
    public static function tautanPenuh(?float $lat, ?float $lng, int $zoom = 17): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        return "https://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=$zoom/$lat/$lng";
    }

    /** Tautan petunjuk arah dari titik asal ke tujuan. */
    public static function rute(?float $dariLat, ?float $dariLng, ?float $keLat, ?float $keLng): ?string
    {
        if (! $dariLat || ! $dariLng || ! $keLat || ! $keLng) {
            return null;
        }

        return "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=$dariLat,$dariLng;$keLat,$keLng";
    }
}
