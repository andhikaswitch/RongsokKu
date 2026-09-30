@props([
    'warna' => 'slate',
    'ikon' => null,
    'kelasKustom' => null,
])

@php
    $peta = [
        'merk' => 'bg-merk-50 text-merk-700 ring-merk-600/20 dark:bg-merk-400/10 dark:text-merk-300 dark:ring-merk-400/30',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
        'lime' => 'bg-lime-50 text-lime-700 ring-lime-600/20 dark:bg-lime-400/10 dark:text-lime-300 dark:ring-lime-400/30',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/30',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-400/10 dark:text-violet-300 dark:ring-violet-400/30',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-400/10 dark:text-slate-300 dark:ring-slate-400/30',
    ];
    $kelas = $kelasKustom ?? ($peta[$warna] ?? $peta['slate']);
@endphp

<span {{ $attributes->merge(['class' =>
    "inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset $kelas"
]) }}>
    @if ($ikon) <x-ikon :nama="$ikon" ukuran="size-3.5" /> @endif
    {{ $slot }}
</span>
