@props([
    'label',
    'nilai',
    'maks' => 100,
    'warna' => 'emerald',
    'keterangan' => null,
    'satuan' => '%',
])

@php
    $persen = $maks > 0 ? max(0, min(100, ($nilai / $maks) * 100)) : 0;

    $bar = [
        'emerald' => 'bg-emerald-500',
        'lime' => 'bg-lime-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'sky' => 'bg-sky-500',
        'merk' => 'bg-merk-500',
    ][$warna] ?? 'bg-slate-400';
@endphp

<div {{ $attributes }}>
    <div class="flex items-baseline justify-between gap-3">
        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ $label }}</span>
        <span class="text-sm font-bold text-slate-900 tabular-nums dark:text-white">
            {{ is_numeric($nilai) ? rtrim(rtrim(number_format((float) $nilai, 1, ',', '.'), '0'), ',') : $nilai }}{{ $satuan }}
        </span>
    </div>

    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
        <div class="h-full rounded-full {{ $bar }} transition-all" style="width: {{ $persen }}%"></div>
    </div>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $keterangan }}</p>
    @endif
</div>
