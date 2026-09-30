@props([
    'aktif' => 1,
    'daftar' => ['Pilih Pengepul', 'Barang', 'Alamat & Jadwal'],
])

<ol {{ $attributes->merge(['class' => 'flex items-center gap-2']) }} aria-label="Langkah pengajuan">
    @foreach ($daftar as $i => $label)
        @php $nomor = $i + 1; @endphp
        <li class="flex flex-1 items-center gap-2">
            <span @class([
                'grid size-8 shrink-0 place-items-center rounded-full text-sm font-bold',
                'bg-merk-600 text-white' => $nomor === $aktif,
                'bg-merk-100 text-merk-700 dark:bg-merk-500/20 dark:text-merk-300' => $nomor < $aktif,
                'bg-slate-100 text-slate-400 dark:bg-slate-800' => $nomor > $aktif,
            ]) @if ($nomor === $aktif) aria-current="step" @endif>
                @if ($nomor < $aktif)
                    <x-ikon nama="cek" ukuran="size-4" />
                @else
                    {{ $nomor }}
                @endif
            </span>
            <span @class([
                'hidden truncate text-sm font-semibold sm:block',
                'text-slate-900 dark:text-white' => $nomor === $aktif,
                'text-slate-500 dark:text-slate-400' => $nomor !== $aktif,
            ])>{{ $label }}</span>
            @unless ($loop->last)
                <span class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></span>
            @endunless
        </li>
    @endforeach
</ol>
