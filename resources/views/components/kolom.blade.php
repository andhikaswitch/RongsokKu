@props([
    'label' => null,
    'nama',
    'tipe' => 'text',
    'wajib' => false,
    'bantuan' => null,
    'awalan' => null,
    'akhiran' => null,
    'nilai' => null,
])

@php
    $id = $attributes->get('id', $nama);
    $galat = $errors->first($nama);
    $isi = old($nama, $nilai);

    $kelasKolom = 'block w-full rounded-xl border-0 bg-white py-2.5 text-sm text-slate-900
                   ring-1 ring-inset shadow-sm transition
                   placeholder:text-slate-400
                   focus:ring-2 focus:ring-inset
                   dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500'
        .($galat
            ? ' ring-rose-400 focus:ring-rose-500 dark:ring-rose-500/50'
            : ' ring-slate-200 focus:ring-merk-600 dark:ring-slate-700 dark:focus:ring-merk-400')
        .($awalan ? ' pl-10' : ' pl-3.5')
        .($akhiran ? ' pr-12' : ' pr-3.5');
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($wajib) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        @if ($awalan)
            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-sm text-slate-400">
                {{ $awalan }}
            </span>
        @endif

        @if ($tipe === 'textarea')
            <textarea id="{{ $id }}" name="{{ $nama }}"
                {{ $attributes->merge(['class' => $kelasKolom, 'rows' => 3]) }}>{{ $isi }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $nama }}" type="{{ $tipe }}"
                value="{{ $tipe === 'file' ? '' : $isi }}"
                {{ $attributes->merge(['class' => $kelasKolom]) }}>
        @endif

        @if ($akhiran)
            <span class="pointer-events-none absolute inset-y-0 right-0 grid w-12 place-items-center text-sm text-slate-400">
                {{ $akhiran }}
            </span>
        @endif
    </div>

    @if ($galat)
        <p class="mt-1.5 flex items-start gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
            <x-ikon nama="peringatan" ukuran="size-3.5 mt-px shrink-0" />
            {{ $galat }}
        </p>
    @elseif ($bantuan)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $bantuan }}</p>
    @endif
</div>
