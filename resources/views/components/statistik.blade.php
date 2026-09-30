@props([
    'label',
    'nilai',
    'ikon' => null,
    'keterangan' => null,
    'tren' => null,
    'warna' => 'merk',
])

@php
    $warnaIkon = [
        'merk' => 'bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400',
        'nilai' => 'bg-nilai-50 text-nilai-600 dark:bg-nilai-500/10 dark:text-nilai-400',
        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    ][$warna] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
@endphp

<div {{ $attributes->merge(['class' =>
    'rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 shadow-[var(--shadow-lembut)]
     dark:bg-slate-900 dark:ring-slate-800'
]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        @if ($ikon)
            <span class="grid size-9 shrink-0 place-items-center rounded-xl {{ $warnaIkon }}">
                <x-ikon :nama="$ikon" ukuran="size-4.5" />
            </span>
        @endif
    </div>

    <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900 tabular-nums dark:text-white">
        {{ $nilai }}
    </p>

    @if ($keterangan || $tren !== null)
        <div class="mt-1.5 flex items-center gap-2 text-xs">
            @if ($tren !== null)
                @php
                    $naik = $tren > 0;
                    $datar = abs($tren) < 0.05;
                @endphp
                <span @class([
                    'inline-flex items-center gap-0.5 font-semibold tabular-nums',
                    'text-emerald-600 dark:text-emerald-400' => $naik && ! $datar,
                    'text-rose-600 dark:text-rose-400' => ! $naik && ! $datar,
                    'text-slate-500 dark:text-slate-400' => $datar,
                ])>
                    <x-ikon :nama="$datar ? 'setara' : ($naik ? 'naik' : 'turun')" ukuran="size-3.5" />
                    {{ \App\Support\Format::persen($tren) }}
                </span>
            @endif
            @if ($keterangan)
                <span class="text-slate-400 dark:text-slate-500">{{ $keterangan }}</span>
            @endif
        </div>
    @endif
</div>
