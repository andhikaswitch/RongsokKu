@props([
    'ikon' => 'kotak',
    'judul' => 'Belum ada data',
    'pesan' => null,
])

<div {{ $attributes->merge(['class' => 'px-6 py-14 text-center']) }}>
    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <x-ikon :nama="$ikon" ukuran="size-6" />
    </span>

    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">{{ $judul }}</h3>

    @if ($pesan)
        <p class="mx-auto mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $pesan }}</p>
    @endif

    @if (trim($slot) !== '')
        <div class="mt-5 flex justify-center gap-3">{{ $slot }}</div>
    @endif
</div>
