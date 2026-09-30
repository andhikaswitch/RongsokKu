@props([
    'nama' => '?',
    'foto' => null,
    'ukuran' => 'size-10',
])

@php
    $bagian = preg_split('/\s+/', trim($nama)) ?: ['?'];
    $inisial = mb_strtoupper(
        mb_substr($bagian[0] ?? '?', 0, 1)
        .(count($bagian) > 1 ? mb_substr(end($bagian), 0, 1) : '')
    );

    // Warna dipilih deterministik dari nama agar konsisten tiap render.
    $palet = [
        'bg-merk-100 text-merk-700 dark:bg-merk-500/15 dark:text-merk-300',
        'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
        'bg-nilai-100 text-nilai-700 dark:bg-nilai-500/15 dark:text-nilai-400',
        'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    ];
    $warna = $palet[crc32($nama) % count($palet)];
@endphp

@if ($foto)
    <img src="{{ \Illuminate\Support\Facades\Storage::url($foto) }}" alt="{{ $nama }}"
         {{ $attributes->merge(['class' => "$ukuran rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700"]) }}>
@else
    <span {{ $attributes->merge(['class' =>
        "$ukuran grid shrink-0 place-items-center rounded-full font-bold $warna"
    ]) }} aria-hidden="true">
        <span class="text-[0.7em]">{{ $inisial }}</span>
    </span>
@endif
