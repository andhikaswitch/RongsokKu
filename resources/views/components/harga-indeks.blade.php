@props([
    'harga',
    'posisi' => null,
    'satuan' => 'kg',
])

{{--
    Menampilkan harga pengepul beserta posisinya terhadap Indeks RongsokKu.
    Ini label informatif, bukan pemblokir: pasar yang menilai, bukan sistem.
--}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-x-2 gap-y-1']) }}>
    <span class="font-bold text-slate-900 tabular-nums dark:text-white">
        {{ rupiah($harga) }}<span class="text-xs font-medium text-slate-400">/{{ $satuan }}</span>
    </span>

    @if ($posisi)
        @php
            $gaya = match ($posisi['arah']) {
                'atas' => ['ikon' => 'naik', 'kelas' => 'text-emerald-600 dark:text-emerald-400', 'teks' => 'di atas indeks'],
                'bawah' => ['ikon' => 'turun', 'kelas' => 'text-rose-600 dark:text-rose-400', 'teks' => 'di bawah indeks'],
                default => ['ikon' => 'setara', 'kelas' => 'text-slate-500 dark:text-slate-400', 'teks' => 'sesuai indeks'],
            };
        @endphp

        <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $gaya['kelas'] }}">
            <x-ikon :nama="$gaya['ikon']" ukuran="size-3.5" />
            @if ($posisi['arah'] === 'sesuai')
                {{ $gaya['teks'] }}
            @else
                {{ \App\Support\Format::persen(abs($posisi['persen']), false) }} {{ $gaya['teks'] }}
            @endif
        </span>
    @endif
</div>
