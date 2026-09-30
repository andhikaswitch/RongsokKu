@props([
    'id',
    'judul',
    'keterangan' => null,
    'lebar' => 'max-w-lg',
])

{{--
    Modal tanpa JavaScript memakai pseudo-class CSS :target.
    Buka dengan <a href="#{{ id }}">, tutup dengan tautan ke fragmen kosong.
    Fragmen "#_" dipakai agar halaman tidak melompat ke atas saat ditutup.
--}}
<div id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-judul"
     class="fixed inset-0 z-50 hidden items-end justify-center p-4 target:flex sm:items-center">
    <a href="#_" class="absolute inset-0 cursor-default bg-slate-950/60 backdrop-blur-sm" aria-label="Tutup"></a>

    <div {{ $attributes->merge(['class' => "relative w-full $lebar max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800"]) }}
         style="animation: naik 0.25s cubic-bezier(0.22,1,0.36,1) both">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <div>
                <h2 id="{{ $id }}-judul" class="font-bold text-slate-900 dark:text-white">{{ $judul }}</h2>
                @if ($keterangan)
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $keterangan }}</p>
                @endif
            </div>
            <a href="#_" class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white"
               aria-label="Tutup">
                <x-ikon nama="silang" ukuran="size-4" />
            </a>
        </div>

        <div class="p-5">
            {{ $slot }}
        </div>
    </div>
</div>
