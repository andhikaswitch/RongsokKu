@php
    $mutu = $p->mutuKepatuhan();
    $jarakKm = isset($p->jarak) ? (float) $p->jarak : null;
@endphp

<a href="{{ route('pengepul.detail', $p) }}"
   class="group flex flex-col rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 transition
          hover:shadow-[var(--shadow-naik)] hover:ring-merk-300
          dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-merk-700">

    <div class="flex items-start gap-3">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-merk-50 text-merk-600
                     transition group-hover:scale-105 dark:bg-merk-500/10 dark:text-merk-400">
            <x-ikon nama="toko" ukuran="size-6" />
        </span>

        <div class="min-w-0 flex-1">
            <div class="flex items-start gap-1.5">
                <h3 class="truncate font-bold text-slate-900 dark:text-white">{{ $p->nama_usaha }}</h3>
                @if ($p->terverifikasi())
                    <x-ikon nama="perisai" ukuran="size-4"
                            class="mt-0.5 shrink-0 text-merk-600 dark:text-merk-400"
                            title="Terverifikasi" />
                @endif
            </div>

            <p class="mt-0.5 flex items-center gap-1 truncate text-xs text-slate-500 dark:text-slate-400">
                <x-ikon nama="pin" ukuran="size-3.5" class="shrink-0" />
                @if ($jarakKm !== null)
                    <span class="font-semibold text-merk-700 dark:text-merk-400">{{ jarak($jarakKm) }}</span>
                    <span>&middot;</span>
                @endif
                {{ $p->user->wilayah?->nama ?? 'Lokasi belum diisi' }}
            </p>

            <div class="mt-2">
                <x-bintang :nilai="$p->rating_rata" :jumlah="$p->jumlah_ulasan" ukuran="size-3.5" />
            </div>
        </div>
    </div>

    {{-- Metrik objektif: dihitung dari data, bukan penilaian subjektif. --}}
    <div class="mt-4 flex flex-wrap gap-1.5">
        <x-lencana :warna="$mutu['warna']">
            Kepatuhan harga {{ rtrim(rtrim(number_format((float) $p->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',') }}%
        </x-lencana>

        @if ($p->izin_b3)
            <x-lencana warna="violet" ikon="perisai">Izin B3</x-lencana>
        @endif

        @if (! $p->sedang_menerima)
            <x-lencana warna="slate">Tutup sementara</x-lencana>
        @endif
    </div>

    <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-center dark:border-slate-800">
        <div>
            <dt class="text-[10px] uppercase tracking-wide text-slate-400">Transaksi</dt>
            <dd class="mt-0.5 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                {{ number_format($p->total_transaksi, 0, ',', '.') }}
            </dd>
        </div>
        <div class="border-x border-slate-100 dark:border-slate-800">
            <dt class="text-[10px] uppercase tracking-wide text-slate-400">Terkumpul</dt>
            <dd class="mt-0.5 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                {{ number_format($p->total_berat_kg, 0, ',', '.') }} kg
            </dd>
        </div>
        <div>
            <dt class="text-[10px] uppercase tracking-wide text-slate-400">Jam buka</dt>
            <dd class="mt-0.5 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                {{ \Illuminate\Support\Str::substr($p->jam_buka, 0, 5) }}
            </dd>
        </div>
    </dl>
</a>
