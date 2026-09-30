@props([
    'nilai' => 0,
    'jumlah' => null,
    'ukuran' => 'size-4',
    'tampilAngka' => true,
])

@php
    $nilai = (float) $nilai;
    $penuh = (int) floor($nilai);
    // Bintang terakhir diisi sebagian memakai lebar overlay, bukan JavaScript.
    $pecahan = $nilai - $penuh;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex items-center gap-px" role="img"
          aria-label="Rating {{ number_format($nilai, 1, ',', '.') }} dari 5">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $penuh)
                <x-ikon nama="bintang" :ukuran="$ukuran" class="fill-nilai-400 text-nilai-400" />
            @elseif ($i === $penuh + 1 && $pecahan >= 0.15)
                <span class="relative inline-grid">
                    <x-ikon nama="bintang" :ukuran="$ukuran" class="text-slate-300 dark:text-slate-600" />
                    <span class="absolute inset-0 overflow-hidden" style="width: {{ round($pecahan * 100) }}%">
                        <x-ikon nama="bintang" :ukuran="$ukuran" class="fill-nilai-400 text-nilai-400" />
                    </span>
                </span>
            @else
                <x-ikon nama="bintang" :ukuran="$ukuran" class="text-slate-300 dark:text-slate-600" />
            @endif
        @endfor
    </span>

    @if ($tampilAngka)
        <span class="text-sm font-semibold text-slate-700 tabular-nums dark:text-slate-300">
            {{ number_format($nilai, 1, ',', '.') }}
        </span>
    @endif

    @if ($jumlah !== null)
        <span class="text-xs text-slate-400 dark:text-slate-500">({{ $jumlah }})</span>
    @endif
</span>
