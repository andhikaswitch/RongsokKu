@props(['ukuran' => 'size-9', 'teks' => true])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="grid {{ $ukuran }} place-items-center rounded-xl bg-gradient-to-br from-merk-500 to-merk-700 text-white shadow-sm">
        <x-ikon nama="daur-ulang" ukuran="size-[55%]" />
    </span>

    @if ($teks)
        <span class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
            Rongsok<span class="text-merk-600 dark:text-merk-400">Ku</span>
        </span>
    @endif
</span>
