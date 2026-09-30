<x-layouts.publik>
    @section('judul', 'Papan Peringkat Daur Ulang')

    <section class="latar-hero relative overflow-hidden border-b border-slate-200 dark:border-slate-800">
        <div class="pola-titik pointer-events-none absolute inset-0 text-slate-900/[0.05] dark:text-white/[0.04]"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-lencana warna="nilai" ikon="piala" class="mb-4">Diperbarui otomatis</x-lencana>

            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Papan Peringkat Daur Ulang
            </h1>
            <p class="mt-4 max-w-2xl leading-relaxed text-slate-600 dark:text-slate-400">
                Warga yang paling banyak menyelamatkan rongsok dari tempat pembuangan akhir.
                Peringkat dihitung dari berat yang benar-benar ditimbang saat transaksi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Peringkat warga --}}
            <div class="lg:col-span-2">
                <x-kartu judul="20 Warga Teratas">
                    <x-slot:aksi>
                        <form method="GET" action="{{ route('peringkat') }}">
                            <x-pilihan nama="wilayah" :opsi="$kelurahan" :nilai="$wilayahDipilih"
                                       kosong="Semua wilayah" class="w-48 text-xs" />
                            <x-tombol type="submit" variant="sekunder" ukuran="kecil" class="mt-2 w-full">
                                Saring
                            </x-tombol>
                        </form>
                    </x-slot:aksi>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($peringkat as $i => $w)
                            @php
                                $medali = [0 => '🥇', 1 => '🥈', 2 => '🥉'][$i] ?? null;
                            @endphp

                            <div @class([
                                'flex items-center gap-4 py-3.5',
                                'rounded-xl bg-nilai-50/60 px-3 dark:bg-nilai-500/5' => $i < 3,
                            ])>
                                <span class="w-8 shrink-0 text-center">
                                    @if ($medali)
                                        <span class="text-xl">{{ $medali }}</span>
                                    @else
                                        <span class="text-sm font-bold text-slate-400 tabular-nums">{{ $i + 1 }}</span>
                                    @endif
                                </span>

                                <x-avatar :nama="$w->name" :foto="$w->foto_profil" ukuran="size-10" />

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $w->name }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ $w->wilayah?->namaLengkap() ?? '-' }}
                                        &middot; {{ $w->total_transaksi }} transaksi
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="font-bold text-slate-900 tabular-nums dark:text-white">
                                        {{ berat($w->total_berat) }}
                                    </p>
                                    <p class="text-xs text-slate-400">didaur ulang</p>
                                </div>
                            </div>
                        @empty
                            <x-kosong ikon="piala" judul="Belum ada peringkat"
                                      pesan="Peringkat muncul setelah ada transaksi yang selesai di wilayah ini." />
                        @endforelse
                    </div>
                </x-kartu>
            </div>

            <div class="space-y-6">
                {{-- Peringkat wilayah --}}
                <x-kartu judul="Wilayah Paling Aktif">
                    <div class="space-y-3">
                        @php $maksBerat = max(1, (float) ($perWilayah->first()->total_berat ?? 1)); @endphp

                        @forelse ($perWilayah as $i => $w)
                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="truncate text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ $i + 1 }}. {{ $w->nama }}
                                    </span>
                                    <span class="shrink-0 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                                        {{ berat($w->total_berat) }}
                                    </span>
                                </div>
                                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-merk-500"
                                         style="width: {{ round((float) $w->total_berat / $maksBerat * 100) }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $w->jumlah_warga }} warga aktif</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </x-kartu>

                {{-- Lencana --}}
                <x-kartu judul="Lencana yang Bisa Diraih">
                    <div class="space-y-3">
                        @foreach ($lencana as $l)
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl dark:bg-slate-800">
                                    {{ $l->ikon }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $l->nama }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $l->deskripsi }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @guest
                        <x-tombol :href="route('register')" variant="primer" penuh class="mt-5" ikon="daun">
                            Mulai Kumpulkan Lencana
                        </x-tombol>
                    @endguest
                </x-kartu>
            </div>
        </div>
    </section>
</x-layouts.publik>
