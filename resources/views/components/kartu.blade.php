@props([
    'judul' => null,
    'keterangan' => null,
    'padat' => false,
    'interaktif' => false,
])

<div {{ $attributes->merge(['class' =>
    'rounded-2xl bg-white ring-1 ring-slate-200/80 shadow-[var(--shadow-lembut)]
     dark:bg-slate-900 dark:ring-slate-800'
    .($interaktif ? ' transition-all duration-200 hover:shadow-[var(--shadow-naik)] hover:ring-slate-300 dark:hover:ring-slate-700' : '')
]) }}>
    @if ($judul || isset($aksi))
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <div class="min-w-0">
                @if ($judul)
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $judul }}</h3>
                @endif
                @if ($keterangan)
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $keterangan }}</p>
                @endif
            </div>
            @isset($aksi)
                <div class="shrink-0">{{ $aksi }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padat ? 'p-4' : 'p-5' }}">
        {{ $slot }}
    </div>

    @isset($kaki)
        <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-3 rounded-b-2xl dark:border-slate-800 dark:bg-slate-800/40">
            {{ $kaki }}
        </div>
    @endisset
</div>
