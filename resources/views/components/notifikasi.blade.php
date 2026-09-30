{{--
    Pesan flash dari session, dirender server-side tanpa JavaScript.
    Kelas Tailwind ditulis utuh (bukan dirangkai) agar terdeteksi saat build.
--}}
@php
    $gaya = [
        'sukses' => [
            'ikon' => 'cek-lingkar',
            'kotak' => 'bg-emerald-50 ring-emerald-600/20 dark:bg-emerald-400/10 dark:ring-emerald-400/30',
            'ikonWarna' => 'text-emerald-600 dark:text-emerald-400',
            'teks' => 'text-emerald-800 dark:text-emerald-200',
        ],
        'peringatan' => [
            'ikon' => 'peringatan',
            'kotak' => 'bg-amber-50 ring-amber-600/20 dark:bg-amber-400/10 dark:ring-amber-400/30',
            'ikonWarna' => 'text-amber-600 dark:text-amber-400',
            'teks' => 'text-amber-800 dark:text-amber-200',
        ],
        'galat' => [
            'ikon' => 'silang',
            'kotak' => 'bg-rose-50 ring-rose-600/20 dark:bg-rose-400/10 dark:ring-rose-400/30',
            'ikonWarna' => 'text-rose-600 dark:text-rose-400',
            'teks' => 'text-rose-800 dark:text-rose-200',
        ],
        'info' => [
            'ikon' => 'info',
            'kotak' => 'bg-sky-50 ring-sky-600/20 dark:bg-sky-400/10 dark:ring-sky-400/30',
            'ikonWarna' => 'text-sky-600 dark:text-sky-400',
            'teks' => 'text-sky-800 dark:text-sky-200',
        ],
    ];
@endphp

@if (session()->hasAny(array_keys($gaya)) || $errors->any())
    <div class="space-y-3">
        @foreach ($gaya as $kunci => $g)
            @if (session($kunci))
                <div role="alert"
                     class="flex items-start gap-3 rounded-xl p-4 ring-1 ring-inset {{ $g['kotak'] }}"
                     style="animation: naik 0.45s cubic-bezier(0.22,1,0.36,1) both">
                    <x-ikon :nama="$g['ikon']" ukuran="size-5" class="mt-px shrink-0 {{ $g['ikonWarna'] }}" />
                    <p class="text-sm font-medium {{ $g['teks'] }}">{{ session($kunci) }}</p>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div role="alert" class="flex items-start gap-3 rounded-xl p-4 ring-1 ring-inset {{ $gaya['galat']['kotak'] }}">
                <x-ikon nama="peringatan" ukuran="size-5" class="mt-px shrink-0 {{ $gaya['galat']['ikonWarna'] }}" />
                <div class="text-sm {{ $gaya['galat']['teks'] }}">
                    <p class="font-semibold">Periksa kembali isian Anda:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->unique() as $galat)
                            <li>{{ $galat }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif
