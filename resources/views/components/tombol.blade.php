@props([
    'variant' => 'primer',
    'ukuran' => 'sedang',
    'href' => null,
    'ikon' => null,
    'ikonKanan' => null,
    'penuh' => false,
])

@php
    $dasar = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl
              transition-all duration-200 whitespace-nowrap
              disabled:opacity-50 disabled:pointer-events-none
              focus-visible:outline-2 focus-visible:outline-offset-2';

    $gaya = match ($variant) {
        'primer' => 'bg-merk-600 text-white shadow-sm hover:bg-merk-700 hover:shadow-md
                     active:scale-[0.98] focus-visible:outline-merk-600
                     dark:bg-merk-500 dark:hover:bg-merk-400 dark:text-merk-950',
        'sekunder' => 'bg-white text-slate-700 ring-1 ring-slate-200 shadow-sm
                       hover:bg-slate-50 hover:ring-slate-300 active:scale-[0.98]
                       dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700
                       dark:hover:bg-slate-700',
        'halus' => 'bg-merk-50 text-merk-700 hover:bg-merk-100 active:scale-[0.98]
                    dark:bg-merk-500/10 dark:text-merk-300 dark:hover:bg-merk-500/20',
        'bahaya' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700
                     active:scale-[0.98] focus-visible:outline-rose-600',
        'bahaya-halus' => 'bg-rose-50 text-rose-700 hover:bg-rose-100
                           dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20',
        'hantu' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900
                    dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
        'nilai' => 'bg-nilai-500 text-white shadow-sm hover:bg-nilai-600 active:scale-[0.98]',
        default => '',
    };

    $dimensi = match ($ukuran) {
        'kecil' => 'text-xs px-3 py-1.5',
        'sedang' => 'text-sm px-4 py-2.5',
        'besar' => 'text-base px-6 py-3.5',
        default => '',
    };

    $kelas = trim("$dasar $gaya $dimensi".($penuh ? ' w-full' : ''));
    $ukuranIkon = $ukuran === 'kecil' ? 'size-3.5' : ($ukuran === 'besar' ? 'size-5' : 'size-4');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon) <x-ikon :nama="$ikon" :ukuran="$ukuranIkon" /> @endif
        {{ $slot }}
        @if ($ikonKanan) <x-ikon :nama="$ikonKanan" :ukuran="$ukuranIkon" /> @endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $kelas]) }}>
        @if ($ikon) <x-ikon :nama="$ikon" :ukuran="$ukuranIkon" /> @endif
        {{ $slot }}
        @if ($ikonKanan) <x-ikon :nama="$ikonKanan" :ukuran="$ukuranIkon" /> @endif
    </button>
@endif
