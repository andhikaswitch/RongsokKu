@props([
    'lat' => null,
    'lng' => null,
    'tinggi' => 'h-64',
    'judul' => 'Peta lokasi',
])

@php
    $sumber = \App\Support\OsmEmbed::titik((float) $lat, (float) $lng);
    $penuh = \App\Support\OsmEmbed::tautanPenuh((float) $lat, (float) $lng);
    $koordinat = \App\Support\GoogleMaps::koordinat((float) $lat, (float) $lng);
    $google = \App\Support\GoogleMaps::lokasi((float) $lat, (float) $lng);
    $ruteGoogle = \App\Support\GoogleMaps::rute((float) $lat, (float) $lng);
@endphp

@if ($sumber)
    <div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-800']) }}>
        {{-- Embed resmi OpenStreetMap: gratis, tanpa API key, tanpa JavaScript. --}}
        <iframe src="{{ $sumber }}"
                title="{{ $judul }}"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                class="w-full {{ $tinggi }} border-0"></iframe>

        <div class="space-y-2 bg-slate-50 px-3 py-2.5 text-xs dark:bg-slate-800/60">
            {{-- Koordinat bisa disalin lalu ditempel di aplikasi peta mana pun. --}}
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500 dark:text-slate-400">Koordinat</span>
                <span class="select-all rounded-md bg-white px-2 py-0.5 font-mono text-slate-800 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-700"
                      title="Klik untuk memilih, lalu salin">{{ $koordinat }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $ruteGoogle }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 rounded-lg bg-merk-600 px-2.5 py-1.5 font-semibold text-white hover:bg-merk-700 dark:bg-merk-500 dark:text-merk-950 dark:hover:bg-merk-400">
                    <x-ikon nama="truk" ukuran="size-3.5" />
                    Petunjuk arah (Google Maps)
                </a>
                <a href="{{ $google }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 font-semibold text-merk-700 ring-1 ring-merk-200 hover:bg-merk-50 dark:text-merk-300 dark:ring-merk-500/30 dark:hover:bg-merk-500/10">
                    <x-ikon nama="pin" ukuran="size-3.5" />
                    Buka di Google Maps
                </a>
                <a href="{{ $penuh }}" target="_blank" rel="noopener"
                   class="ml-auto inline-flex items-center gap-1 font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                    © OpenStreetMap
                    <x-ikon nama="panah-kanan" ukuran="size-3" />
                </a>
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' =>
        "grid $tinggi place-items-center rounded-2xl bg-slate-100 text-center ring-1 ring-slate-200
         dark:bg-slate-800/50 dark:ring-slate-800"
    ]) }}>
        <div class="px-6">
            <x-ikon nama="pin" ukuran="size-7" class="mx-auto text-slate-400" />
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Koordinat lokasi belum diisi.</p>
        </div>
    </div>
@endif
