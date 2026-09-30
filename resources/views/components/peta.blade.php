@props([
    'lat' => null,
    'lng' => null,
    'tinggi' => 'h-64',
    'judul' => 'Peta lokasi',
])

@php
    $sumber = \App\Support\OsmEmbed::titik((float) $lat, (float) $lng);
    $penuh = \App\Support\OsmEmbed::tautanPenuh((float) $lat, (float) $lng);
@endphp

@if ($sumber)
    <div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-800']) }}>
        {{-- Embed resmi OpenStreetMap: gratis, tanpa API key, tanpa JavaScript. --}}
        <iframe src="{{ $sumber }}"
                title="{{ $judul }}"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                class="w-full {{ $tinggi }} border-0"></iframe>

        <div class="flex items-center justify-between gap-3 bg-slate-50 px-3 py-2 text-xs dark:bg-slate-800/60">
            <span class="text-slate-400 dark:text-slate-500">© OpenStreetMap</span>
            <a href="{{ $penuh }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1 font-semibold text-merk-700 hover:text-merk-800 dark:text-merk-400 dark:hover:text-merk-300">
                Buka peta besar
                <x-ikon nama="panah-kanan" ukuran="size-3" />
            </a>
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
