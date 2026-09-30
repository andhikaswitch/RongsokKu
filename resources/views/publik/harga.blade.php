<x-layouts.publik>
    @section('judul', 'Pusat Harga Pasar Rongsok')
    @section('deskripsi', 'Indeks harga rongsok dihitung dari transaksi nyata, dilengkapi harga acuan resmi yang bisa ditelusuri sumbernya.')

    {{-- ══ KEPALA ══ --}}
    <section class="latar-hero relative overflow-hidden border-b border-slate-200 dark:border-slate-800">
        <div class="pola-titik pointer-events-none absolute inset-0 text-slate-900/[0.05] dark:text-white/[0.04]"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-lencana warna="merk" ikon="grafik" class="mb-4">Diperbarui setiap hari</x-lencana>

            <h1 class="max-w-3xl text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Pusat Harga Pasar Rongsok
            </h1>

            <p class="mt-4 max-w-2xl leading-relaxed text-slate-600 dark:text-slate-400">
                Angka di halaman ini bukan tebakan. <strong class="font-semibold text-slate-900 dark:text-white">Indeks
                RongsokKu</strong> dihitung dari harga tempat transaksi benar-benar terjadi di platform ini,
                lalu dibandingkan dengan harga acuan dari sumber yang bisa ditelusuri.
            </p>

            {{-- Cara baca halaman ini, memakai <details> bawaan HTML. --}}
            <details class="group mt-6 max-w-2xl">
                <summary class="inline-flex cursor-pointer list-none items-center gap-2 text-sm font-semibold
                                text-merk-700 hover:text-merk-800 dark:text-merk-400">
                    <x-ikon nama="info" ukuran="size-4" />
                    Bagaimana cara membaca angka ini?
                    <x-ikon nama="panah-kanan" ukuran="size-3.5" class="transition group-open:rotate-90" />
                </summary>

                <div class="mt-4 space-y-3 rounded-2xl bg-white/70 p-5 text-sm leading-relaxed text-slate-600
                            ring-1 ring-slate-200/60 backdrop-blur dark:bg-slate-900/70 dark:text-slate-400 dark:ring-slate-700/60">
                    <p>
                        <strong class="font-semibold text-slate-900 dark:text-white">Indeks RongsokKu</strong>
                        adalah nilai tengah (median) dari harga seluruh transaksi selesai dalam 30 hari terakhir.
                        Median dipakai, bukan rata-rata, supaya satu transaksi tidak wajar tidak menggeser angkanya.
                        Indeks baru ditampilkan setelah terkumpul minimal {{ \App\Models\IndeksHarga::MIN_TRANSAKSI }} transaksi.
                    </p>
                    <p>
                        <strong class="font-semibold text-slate-900 dark:text-white">Harga acuan</strong>
                        dicatat admin dari sumber luar seperti bank sampah induk atau pabrik daur ulang,
                        lengkap dengan tanggal dan nama sumbernya.
                    </p>
                    <p>
                        Pengepul <em>bebas</em> menetapkan harganya sendiri. Bila harganya menyimpang jauh dari
                        indeks, sistem hanya memberi label peringatan agar Anda bisa menilai sendiri.
                    </p>
                </div>
            </details>
        </div>
    </section>

    {{-- ══ SARING GOLONGAN (form GET, tanpa JavaScript) ══ --}}
    <section class="sticky top-16 z-20 border-b border-slate-200 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/90">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="geser-rapi flex gap-2 overflow-x-auto py-3">
                <a href="{{ route('harga.index') }}"
                   @class([
                       'shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition',
                       'bg-merk-600 text-white' => ! $golonganDipilih,
                       'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' => $golonganDipilih,
                   ])>
                    Semua
                </a>

                @foreach ($golongan as $g)
                    <a href="{{ route('harga.index', ['golongan' => $g->slug]) }}"
                       @class([
                           'shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition',
                           'bg-merk-600 text-white' => $golonganDipilih === $g->slug,
                           'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' => $golonganDipilih !== $g->slug,
                       ])>
                        {{ $g->ikon }} {{ $g->nama }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ DAFTAR HARGA ══ --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($kategori as $k)
                @php
                    $idx = $indeksTerbaru[$k->id] ?? null;
                    $cukup = $idx && $idx->jumlah_transaksi >= \App\Models\IndeksHarga::MIN_TRANSAKSI;
                    $acu = $acuan[$k->id] ?? null;
                    $tren = ($riwayat[$k->id] ?? collect())->pluck('harga_median')->all();
                @endphp

                <a href="{{ route('harga.show', $k) }}"
                   class="group flex flex-col rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 transition
                          hover:shadow-[var(--shadow-naik)] hover:ring-merk-300
                          dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-merk-700">

                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-slate-100 text-xl
                                     transition group-hover:scale-105 dark:bg-slate-800">
                            {{ $k->ikon }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate font-bold text-slate-900 dark:text-white">{{ $k->nama }}</h2>
                            <p class="text-xs text-slate-400">{{ $k->induk?->nama }}</p>
                        </div>
                        @if ($k->limbah_b3)
                            <x-lencana warna="rose">B3</x-lencana>
                        @endif
                    </div>

                    {{-- Lapis 1: Indeks dari transaksi nyata --}}
                    <div class="mt-5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-merk-700 dark:text-merk-400">
                            Indeks RongsokKu
                        </p>

                        @if ($cukup)
                            <div class="mt-1 flex items-baseline gap-2">
                                <span class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                    {{ rupiah($idx->harga_median) }}
                                </span>
                                <span class="text-xs font-medium text-slate-400">/{{ $k->satuan }}</span>

                                @if ($idx->perubahan_persen !== null && abs((float) $idx->perubahan_persen) >= 0.05)
                                    <span @class([
                                        'ml-auto inline-flex items-center gap-0.5 text-xs font-bold tabular-nums',
                                        'text-emerald-600 dark:text-emerald-400' => $idx->perubahan_persen > 0,
                                        'text-rose-600 dark:text-rose-400' => $idx->perubahan_persen < 0,
                                    ])>
                                        <x-ikon :nama="$idx->perubahan_persen > 0 ? 'naik' : 'turun'" ukuran="size-3.5" />
                                        {{ \App\Support\Format::persen((float) $idx->perubahan_persen) }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Dari <strong class="font-semibold">{{ $idx->jumlah_transaksi }} transaksi</strong>
                                &middot; {{ berat($idx->total_berat_kg) }}
                            </p>

                            @if (count($tren) >= 2)
                                <div class="mt-3">
                                    <x-sparkline :data="$tren" tinggi="h-12" />
                                </div>
                            @endif
                        @else
                            <div class="mt-1 rounded-xl bg-slate-50 px-3 py-3 dark:bg-slate-800/50">
                                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Data belum cukup</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    Baru {{ $idx?->jumlah_transaksi ?? 0 }} dari
                                    {{ \App\Models\IndeksHarga::MIN_TRANSAKSI }} transaksi minimum.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Lapis 2: Acuan resmi --}}
                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Acuan Resmi</p>

                        @if ($acu)
                            <p class="mt-1 text-sm font-bold text-slate-700 tabular-nums dark:text-slate-300">
                                {{ rupiah($acu->harga_min) }} – {{ rupiah($acu->harga_max) }}
                            </p>
                            <p class="mt-1 flex items-center gap-1 truncate text-xs text-slate-400">
                                <x-ikon nama="dokumen" ukuran="size-3" class="shrink-0" />
                                {{ $acu->sumber_nama }} &middot; {{ tanggal_id($acu->berlaku_mulai) }}
                            </p>
                        @else
                            <p class="mt-1 text-sm text-slate-400">Belum ada acuan tercatat.</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 lg:col-span-3">
                    <x-kartu>
                        <x-kosong ikon="grafik" judul="Belum ada kategori"
                                  pesan="Kategori pada golongan ini belum tersedia." />
                    </x-kartu>
                </div>
            @endforelse
        </div>

        {{-- Catatan metodologi, penting untuk laporan --}}
        <div class="mt-12 rounded-2xl bg-slate-100 p-6 dark:bg-slate-800/50">
            <h2 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                <x-ikon nama="info" ukuran="size-5" class="text-slate-400" />
                Catatan metodologi
            </h2>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Sampai saat ini belum ada satu lembaga resmi di Indonesia yang menerbitkan harga rongsok
                secara berkala dan terbuka untuk publik. Karena itu RongsokKu menyusun indeksnya sendiri
                dari transaksi yang tercatat di platform, dan melengkapinya dengan harga acuan dari sumber
                lokal yang setiap angkanya dicantumkan asal-usulnya. Pendekatan ini dipilih agar harga tetap
                bisa dipertanggungjawabkan tanpa bergantung pada data yang belum tersedia.
            </p>
        </div>
    </section>
</x-layouts.publik>
