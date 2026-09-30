@props([
    'label' => null,
    'nama',
    'wajib' => false,
    'bantuan' => 'JPG atau PNG, maksimal 2 MB.',
    'accept' => 'image/jpeg,image/png,image/webp',
    'sudahAda' => null,
])

@php $galat = $errors->first($nama); @endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $nama }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($wajib) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    <input type="file" id="{{ $nama }}" name="{{ $nama }}" accept="{{ $accept }}"
           @if ($wajib) required @endif
           {{ $attributes->merge(['class' =>
               'block w-full cursor-pointer rounded-xl bg-white text-sm text-slate-600 ring-1 ring-inset shadow-sm
                file:mr-3 file:cursor-pointer file:border-0 file:bg-merk-50 file:px-4 file:py-2.5 file:text-sm
                file:font-semibold file:text-merk-700 hover:file:bg-merk-100
                dark:bg-slate-800 dark:text-slate-300 dark:file:bg-merk-500/10 dark:file:text-merk-300'
               .($galat ? ' ring-rose-400' : ' ring-slate-200 dark:ring-slate-700')
           ]) }}>

    @if ($sudahAda)
        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
            <x-ikon nama="cek" ukuran="size-3.5" /> {{ $sudahAda }}
        </p>
    @endif

    @if ($galat)
        <p class="mt-1.5 flex items-start gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
            <x-ikon nama="peringatan" ukuran="size-3.5 mt-px shrink-0" /> {{ $galat }}
        </p>
    @elseif ($bantuan)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $bantuan }}</p>
    @endif
</div>
