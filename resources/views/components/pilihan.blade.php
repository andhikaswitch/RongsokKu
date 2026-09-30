@props([
    'label' => null,
    'nama',
    'opsi' => [],
    'kosong' => 'Pilih salah satu',
    'wajib' => false,
    'bantuan' => null,
    'nilai' => null,
])

@php
    $id = $attributes->get('id', $nama);
    $galat = $errors->first($nama);
    $terpilih = (string) old($nama, $nilai);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($wajib) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    <select id="{{ $id }}" name="{{ $nama }}"
        {{ $attributes->merge(['class' =>
            'block w-full rounded-xl border-0 bg-white py-2.5 pl-3.5 pr-9 text-sm text-slate-900
             ring-1 ring-inset shadow-sm transition
             focus:ring-2 focus:ring-inset
             dark:bg-slate-800 dark:text-white'
            .($galat
                ? ' ring-rose-400 focus:ring-rose-500'
                : ' ring-slate-200 focus:ring-merk-600 dark:ring-slate-700 dark:focus:ring-merk-400')
        ]) }}>
        @if ($kosong !== false)
            <option value="">{{ $kosong }}</option>
        @endif

        @foreach ($opsi as $kunci => $teks)
            <option value="{{ $kunci }}" @selected($terpilih === (string) $kunci)>{{ $teks }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if ($galat)
        <p class="mt-1.5 flex items-start gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
            <x-ikon nama="peringatan" ukuran="size-3.5 mt-px shrink-0" />
            {{ $galat }}
        </p>
    @elseif ($bantuan)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $bantuan }}</p>
    @endif
</div>
