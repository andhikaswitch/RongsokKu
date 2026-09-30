<x-layouts.publik>
    @section('judul', 'Harga '.$kategori->nama)

    @php
        $cukup = $indeks && $indeks->jumlah_transaksi >= \App\Models\IndeksHarga::MIN_TRANSAKSI;
        $tren = $riwayat->pluck('harga_median')->all();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('harga.index') }}" class="hover:text-merk-700 dark:hover:text-merk-400">Harga Pasar</a>
            <span>/</span>
            <a href="{{ route('harga.index', ['golongan' => $kategori->induk?->slug]) }}"
               class="hover:text-merk-700 dark:hover:text-merk-400">{{ $kategori->induk?->nama }}</a>
            <span>/</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ $kategori->nama }}</span>
        </nav>

        <div class="mt-6 flex flex-wrap items-start gap-5">
            <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-white text-3xl shadow-sm
                         ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                {{ $kategori->ikon }}
            </span>

            <div class="min-w-0 flex-1">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    {{ $kategori->nama }}
                </h1>
                <p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-400">{{ $kategori->deskripsi }}</p>
            </div>
        </div>

        {{-- Peringatan limbah B3 --}}
        @if ($kategori->limbah_b3)
            <div class="mt-6 flex items-start gap-4 rounded-2xl bg-rose-50 p-5 ring-1 ring-inset ring-rose-600/20
                        dark:bg-rose-500/10 dark:ring-rose-400/30">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                    <x-ikon nama="peringatan" ukuran="size-5" />
                </span>
                <div>
                    <h2 class="font-bold text-rose-900 dark:text-rose-200">Limbah B3 — Berbahaya dan Beracun</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-rose-800 dark:text-rose-300">
                        {{ $kategori->peringatan_b3 }}
                    </p>
                </div>
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            {{-- Kolom kiri: indeks & tren --}}
            <div class="space-y-6 lg:col-span-2">
                <x-kartu judul="Indeks RongsokKu" keterangan="Nilai tengah dari transaksi 30 hari terakhir">
                    @if ($cukup)
                        <div class="flex flex-wrap items-end gap-x-6 gap-y-3">
                            <div>
                                <p class="text-4xl font-extrabold tracking-tight text-slate-900 tabular-nums dark:text-white">
                                    {{ rupiah($indeks->harga_median) }}
                                    <span class="text-base font-bold text-slate-400">/{{ $kategori->satuan }}</span>
                                </p>
                                @if ($indeks->perubahan_persen !== null)
                                    <p @class([
                                        'mt-1 inline-flex items-center gap-1 text-sm font-bold tabular-nums',
                                        'text-emerald-600 dark:text-emerald-400' => $indeks->perubahan_persen > 0,
                                        'text-rose-600 dark:text-rose-400' => $indeks->perubahan_persen < 0,
                                        'text-slate-500' => abs((float) $indeks->perubahan_persen) < 0.05,
                                    ])>
                                        <x-ikon :nama="$indeks->perubahan_persen > 0 ? 'naik' : ($indeks->perubahan_persen < 0 ? 'turun' : 'setara')" ukuran="size-4" />
                                        {{ \App\Support\Format::persen((float) $indeks->perubahan_persen) }} dari sebelumnya
                                    </p>
                                @endif
                            </div>

                            <dl class="ml-auto grid grid-cols-3 gap-5 text-right">
                                <div>
                                    <dt class="text-xs text-slate-400">Terendah</dt>
                                    <dd class="mt-0.5 font-bold text-slate-700 tabular-nums dark:text-slate-300">{{ rupiah($indeks->harga_min) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Tertinggi</dt>
                                    <dd class="mt-0.5 font-bold text-slate-700 tabular-nums dark:text-slate-300">{{ rupiah($indeks->harga_max) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Transaksi</dt>
                                    <dd class="mt-0.5 font-bold text-slate-700 tabular-nums dark:text-slate-300">{{ $indeks->jumlah_transaksi }}</dd>
                                </div>
                            </dl>
                        </div>

                        @if (count($tren) >= 2)
                            <div class="mt-7">
                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Tren {{ count($tren) }} hari terakhir
                                </p>
                                <x-sparkline :data="$tren" tinggi="h-32" />
                                <div class="mt-1.5 flex justify-between text-xs text-slate-400">
                                    <span>{{ tanggal_id($riwayat->first()?->tanggal) }}</span>
                                    <span>{{ tanggal_id($riwayat->last()?->tanggal) }}</span>
                                </div>
                            </div>
                        @endif
                    @else
                        <x-kosong ikon="grafik" judul="Data belum cukup"
                                  :pesan="'Indeks ditampilkan setelah terkumpul minimal '.\App\Models\IndeksHarga::MIN_TRANSAKSI.' transaksi selesai. Saat ini baru ada '.($indeks?->jumlah_transaksi ?? 0).'.'" />
                    @endif
                </x-kartu>

                {{-- Daftar pengepul yang menerima kategori ini --}}
                <x-kartu :judul="'Pengepul yang menerima '.$kategori->nama"
                         keterangan="Diurutkan dari harga tertinggi">
                    @forelse ($pengepul as $h)
                        @php $posisi = $h->posisiTerhadapIndeks(); @endphp

                        <a href="{{ route('pengepul.detail', $h->pengepul) }}"
                           class="flex items-center gap-4 rounded-xl px-2 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                                <x-ikon nama="toko" ukuran="size-5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">
                                    {{ $h->pengepul->nama_usaha }}
                                </p>
                                <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-slate-500 dark:text-slate-400">
                                    <x-ikon nama="pin" ukuran="size-3" />
                                    {{ $h->pengepul->user->wilayah?->nama }}
                                    <span>&middot;</span>
                                    <x-bintang :nilai="$h->pengepul->rating_rata" ukuran="size-3" :tampil-angka="true" />
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <x-harga-indeks :harga="$h->harga_per_satuan" :posisi="$posisi" :satuan="$kategori->satuan"
                                                class="flex-col items-end gap-0.5" />
                                @if ($h->min_berat > 0)
                                    <p class="mt-0.5 text-xs text-slate-400">min. {{ berat($h->min_berat) }}</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <x-kosong ikon="toko" judul="Belum ada pengepul"
                                  pesan="Belum ada pengepul terverifikasi yang memasang harga untuk kategori ini." />
                    @endforelse
                </x-kartu>
            </div>

            {{-- Kolom kanan: acuan & dampak --}}
            <div class="space-y-6">
                <x-kartu judul="Harga Acuan Resmi">
                    @if ($acuan)
                        <p class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                            {{ rupiah($acuan->harga_min) }} – {{ rupiah($acuan->harga_max) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">per {{ $kategori->satuan }}</p>

                        <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 dark:text-slate-400">Sumber</dt>
                                <dd class="text-right font-semibold text-slate-900 dark:text-white">{{ $acuan->sumber_nama }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 dark:text-slate-400">Jenis</dt>
                                <dd class="text-right font-medium text-slate-700 dark:text-slate-300">{{ $acuan->sumber_tipe->label() }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 dark:text-slate-400">Berlaku sejak</dt>
                                <dd class="text-right font-medium text-slate-700 dark:text-slate-300">{{ tanggal_id($acuan->berlaku_mulai) }}</dd>
                            </div>
                        </dl>

                        @if ($acuan->dokumen_bukti)
                            <x-tombol :href="\Illuminate\Support\Facades\Storage::url($acuan->dokumen_bukti)"
                                      variant="sekunder" ukuran="kecil" penuh ikon="dokumen" class="mt-4" target="_blank">
                                Lihat dokumen sumber
                            </x-tombol>
                        @endif
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Belum ada harga acuan resmi yang tercatat untuk kategori ini.
                        </p>
                    @endif
                </x-kartu>

                <x-kartu judul="Dampak Lingkungan">
                    <div class="flex items-center gap-4">
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                            <x-ikon nama="daun" ukuran="size-6" />
                        </span>
                        <div>
                            <p class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ number_format($kategori->faktor_co2_per_kg, 2, ',', '.') }} kg
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">CO₂ dicegah per kg didaur ulang</p>
                        </div>
                    </div>

                    <p class="mt-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Angka ini adalah estimasi berdasarkan faktor emisi umum untuk material
                        {{ \Illuminate\Support\Str::lower($kategori->induk?->nama ?? $kategori->nama) }}.
                    </p>
                </x-kartu>

                <x-kartu>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Punya {{ \Illuminate\Support\Str::lower($kategori->nama) }} di rumah?
                        Ajukan penjemputan dan biarkan pengepul yang datang.
                    </p>
                    <x-tombol :href="route('pengepul.cari', ['kategori' => $kategori->id])"
                              variant="primer" penuh ikon="cari" class="mt-4">
                        Cari Pengepul
                    </x-tombol>
                </x-kartu>
            </div>
        </div>
    </section>
</x-layouts.publik>
