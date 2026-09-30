@props([
    'data' => [],
    'tinggi' => 'h-16',
    'warna' => 'text-merk-500',
])

@php
    // Grafik dirender sebagai SVG di sisi server. SVG adalah HTML,
    // sehingga batasan "tanpa JavaScript" tetap terpenuhi.
    $nilai = array_values(array_map('floatval', $data));
    $n = count($nilai);
    $min = $n ? min($nilai) : 0;
    $max = $n ? max($nilai) : 0;
    $rentang = ($max - $min) > 0 ? ($max - $min) : 1;
    $lebar = 100;
    $tinggiViewBox = 32;
    $jarakX = $n > 1 ? $lebar / ($n - 1) : 0;

    $titik = [];
    foreach ($nilai as $i => $v) {
        $x = round($i * $jarakX, 2);
        $y = round($tinggiViewBox - (($v - $min) / $rentang) * ($tinggiViewBox - 4) - 2, 2);
        $titik[] = "$x,$y";
    }
    $garis = implode(' ', $titik);
    $area = $n ? "0,$tinggiViewBox ".$garis." $lebar,$tinggiViewBox" : '';
    $gradienId = 'grad-'.substr(md5($garis.microtime()), 0, 8);
@endphp

@if ($n >= 2)
    <svg viewBox="0 0 {{ $lebar }} {{ $tinggiViewBox }}" preserveAspectRatio="none"
         {{ $attributes->merge(['class' => "w-full $tinggi $warna"]) }} aria-hidden="true">
        <defs>
            <linearGradient id="{{ $gradienId }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="currentColor" stop-opacity="0.25" />
                <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
            </linearGradient>
        </defs>
        <polygon points="{{ $area }}" fill="url(#{{ $gradienId }})" />
        <polyline points="{{ $garis }}" fill="none" stroke="currentColor"
                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                  vector-effect="non-scaling-stroke" />
    </svg>
@else
    <div class="{{ $tinggi }} grid place-items-center rounded-lg bg-slate-50 dark:bg-slate-800/50">
        <span class="text-xs text-slate-400">Data belum cukup</span>
    </div>
@endif
