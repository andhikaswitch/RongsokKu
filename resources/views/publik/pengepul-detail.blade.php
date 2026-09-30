<x-layouts.publik>
    @section('judul', $pengepul->nama_usaha)

    @php
        $mutu = $pengepul->mutuKepatuhan();
        $u = $pengepul->user;
        $totalUlasan = max(1, $pengepul->jumlah_ulasan);
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('pengepul.cari') }}" class="hover:text-merk-700 dark:hover:text-merk-400">Cari Pengepul</a>
            <span>/</span>
            <span class="truncate font-medium text-slate-900 dark:text-white">{{ $pengepul->nama_usaha }}</span>
        </nav>

        {{-- ══ KEPALA PROFIL ══ --}}
        <div class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-slate-200/80 sm:p-8 dark:bg-slate-900 dark:ring-slate-800">
            <div class="flex flex-wrap items-start gap-5">
                <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                    <x-ikon nama="toko" ukuran="size-8" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            {{ $pengepul->nama_usaha }}
                        </h1>
                        @if ($pengepul->terverifikasi())
                            <x-lencana warna="merk" ikon="perisai">Terverifikasi</x-lencana>
                        @endif
                        @if ($pengepul->izin_b3)
                            <x-lencana warna="violet" ikon="perisai">Izin B3</x-lencana>
                        @endif
                    </div>

                    <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1">
                            <x-ikon nama="pin" ukuran="size-4" />
                            {{ $u->wilayah?->namaLengkap() ?? 'Lokasi belum diisi' }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <x-ikon nama="jam" ukuran="size-4" />
                            {{ \Illuminate\Support\Str::substr($pengepul->jam_buka, 0, 5) }}
                            –
                            {{ \Illuminate\Support\Str::substr($pengepul->jam_tutup, 0, 5) }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <x-ikon nama="truk" ukuran="size-4" />
                            Radius {{ $pengepul->radius_layanan_km }} km
                        </span>
                    </p>

                    <div class="mt-3">
                        <x-bintang :nilai="$pengepul->rating_rata" :jumlah="$pengepul->jumlah_ulasan" />
                    </div>

                    @if ($pengepul->deskripsi)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $pengepul->deskripsi }}
                        </p>
                    @endif
                </div>

                <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto">
                    @auth
                        @if (auth()->user()->isWarga())
                            <x-tombol :href="route('warga.ajukan', ['pengepul' => $pengepul->slug])"
                                      variant="primer" ukuran="besar" ikon="truk">
                                Ajukan Jemput
                            </x-tombol>
                        @endif
                    @else
                        <x-tombol :href="route('register')" variant="primer" ukuran="besar" ikon="truk">
                            Ajukan Jemput
                        </x-tombol>
                    @endauth

                    @if ($pengepul->sedang_menerima)
                        <p class="text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            ● Sedang menerima
                        </p>
                    @else
                        <p class="text-center text-xs font-semibold text-slate-400">● Tutup sementara</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══ METRIK OBJEKTIF ══ --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-statistik label="Transaksi Selesai" :nilai="number_format($pengepul->total_transaksi, 0, ',', '.')"
                         ikon="cek-lingkar" warna="merk" />
            <x-statistik label="Total Terkumpul" :nilai="berat($pengepul->total_berat_kg)"
                         ikon="timbangan" warna="sky" />
            <x-statistik label="Tingkat Penerimaan" :nilai="rtrim(rtrim(number_format((float) $pengepul->tingkat_penerimaan, 1, ',', '.'), '0'), ',').'%'"
                         ikon="lonceng" warna="violet" />
            <x-statistik label="Ketepatan Waktu" :nilai="rtrim(rtrim(number_format((float) $pengepul->ketepatan_waktu, 1, ',', '.'), '0'), ',').'%'"
                         ikon="jam" warna="nilai" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- ══ KEPATUHAN HARGA ══ --}}
                <x-kartu judul="Kepatuhan Harga"
                         keterangan="Dihitung otomatis dari data transaksi, bukan dari penilaian warga">
                    <div class="flex flex-wrap items-center gap-5">
                        <div>
                            <p class="text-4xl font-extrabold tracking-tight text-slate-900 tabular-nums dark:text-white">
                                {{ rtrim(rtrim(number_format((float) $pengepul->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',') }}%
                            </p>
                            <x-lencana :warna="$mutu['warna']" class="mt-2">
                                {{ $mutu['titik'] }} {{ $mutu['label'] }}
                            </x-lencana>
                        </div>

                        <div class="min-w-[14rem] flex-1">
                            <x-meter label="Skor kepatuhan" :nilai="$pengepul->skor_kepatuhan_harga"
                                     :warna="$mutu['warna']" />
                        </div>
                    </div>

                    <p class="mt-5 rounded-xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-600 dark:bg-slate-800/50 dark:text-slate-400">
                        Angka ini menunjukkan berapa persen transaksi yang <strong class="font-semibold text-slate-900 dark:text-white">dibayar
                        sesuai atau di atas harga yang dipajang</strong> saat warga mengajukan penjemputan.
                        Harga dikunci ketika permintaan dibuat, sehingga perubahan daftar harga setelahnya
                        tidak memengaruhi kesepakatan yang sedang berjalan.
                    </p>

                    @if ($pengepul->pembatalan_sepihak > 0)
                        <p class="mt-3 flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400">
                            <x-ikon nama="peringatan" ukuran="size-4" />
                            Pernah membatalkan {{ $pengepul->pembatalan_sepihak }} permintaan secara sepihak.
                        </p>
                    @endif
                </x-kartu>

                {{-- ══ DAFTAR HARGA ══ --}}
                <x-kartu judul="Daftar Harga" keterangan="Dibandingkan dengan Indeks RongsokKu">
                    @forelse ($hargaPerGolongan as $namaGolongan => $daftar)
                        <div class="@if (! $loop->first) mt-6 @endif">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                                <span>{{ $daftar->first()->kategori->induk?->ikon }}</span>
                                {{ $namaGolongan }}
                            </h3>

                            <div class="mt-2 divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($daftar as $h)
                                    @php $posisi = $h->posisiTerhadapIndeks(); @endphp
                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-300">
                                                {{ $h->kategori->nama }}
                                                @if ($h->kategori->limbah_b3)
                                                    <span class="text-rose-500" title="Limbah B3">⚠️</span>
                                                @endif
                                            </p>
                                            @if ($h->min_berat > 0)
                                                <p class="text-xs text-slate-400">Minimal {{ berat($h->min_berat) }}</p>
                                            @endif
                                        </div>

                                        <x-harga-indeks :harga="$h->harga_per_satuan" :posisi="$posisi"
                                                        :satuan="$h->kategori->satuan"
                                                        class="shrink-0 flex-col items-end gap-0.5 text-right" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <x-kosong ikon="grafik" judul="Belum ada harga"
                                  pesan="Pengepul ini belum memasang daftar harganya." />
                    @endforelse
                </x-kartu>

                {{-- ══ ULASAN ══ --}}
                <x-kartu judul="Ulasan Warga" :keterangan="$pengepul->jumlah_ulasan.' ulasan diterima'">
                    @if ($sebaranRating->isNotEmpty())
                        <div class="mb-6 space-y-1.5">
                            @for ($b = 5; $b >= 1; $b--)
                                @php $jml = $sebaranRating[$b] ?? 0; @endphp
                                <div class="flex items-center gap-3">
                                    <span class="flex w-8 shrink-0 items-center gap-0.5 text-xs font-medium text-slate-500">
                                        {{ $b }}
                                        <x-ikon nama="bintang" ukuran="size-3" class="fill-nilai-400 text-nilai-400" />
                                    </span>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-nilai-400"
                                             style="width: {{ round($jml / $totalUlasan * 100) }}%"></div>
                                    </div>
                                    <span class="w-8 shrink-0 text-right text-xs tabular-nums text-slate-400">{{ $jml }}</span>
                                </div>
                            @endfor
                        </div>
                    @endif

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($ulasan as $u2)
                            <div class="py-4 first:pt-0">
                                <div class="flex items-start gap-3">
                                    <x-avatar :nama="$u2->warga->name" ukuran="size-9" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $u2->warga->name }}
                                            </span>
                                            <span class="text-xs text-slate-400">{{ tanggal_id($u2->created_at) }}</span>
                                        </div>
                                        <div class="mt-1">
                                            <x-bintang :nilai="$u2->rating" ukuran="size-3.5" :tampil-angka="false" />
                                        </div>
                                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                            {{ $u2->komentar }}
                                        </p>

                                        @if ($u2->balasan)
                                            <div class="mt-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                    Balasan {{ $pengepul->nama_usaha }}
                                                </p>
                                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $u2->balasan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-kosong ikon="bintang" judul="Belum ada ulasan"
                                      pesan="Ulasan akan muncul setelah ada transaksi yang selesai." />
                        @endforelse
                    </div>

                    @if ($ulasan->hasPages())
                        <div class="mt-5">{{ $ulasan->links() }}</div>
                    @endif
                </x-kartu>
            </div>

            {{-- ══ SISI KANAN ══ --}}
            <div class="space-y-6">
                <x-kartu judul="Lokasi Lapak" padat>
                    <x-peta :lat="$u->latitude" :lng="$u->longitude" tinggi="h-56"
                            :judul="'Peta lokasi '.$pengepul->nama_usaha" />

                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ $u->alamat_detail }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $u->wilayah?->namaLengkap() }}</p>
                </x-kartu>

                @auth
                    @php $wa = \App\Support\Format::nomorWa($u->telepon); @endphp
                    @if ($wa)
                        <x-kartu judul="Hubungi Langsung">
                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                Ada yang ingin ditanyakan sebelum mengajukan penjemputan?
                            </p>
                            <x-tombol href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo '.$pengepul->nama_usaha.', saya menghubungi Anda lewat RongsokKu. Saya ingin bertanya soal ') }}"
                                      target="_blank" rel="noopener"
                                      variant="primer" penuh ikon="wa" class="mt-4">
                                Chat via WhatsApp
                            </x-tombol>
                        </x-kartu>
                    @endif
                @else
                    <x-kartu>
                        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Masuk untuk melihat kontak WhatsApp pengepul dan mengajukan penjemputan.
                        </p>
                        <x-tombol :href="route('login')" variant="primer" penuh ikon="masuk" class="mt-4">
                            Masuk
                        </x-tombol>
                    </x-kartu>
                @endauth

                @if ($pengepul->diverifikasi_pada)
                    <x-kartu>
                        <div class="flex items-start gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                                <x-ikon nama="perisai" ukuran="size-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Sudah diverifikasi</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Identitas dan lokasi lapak diperiksa admin pada
                                    {{ tanggal_id($pengepul->diverifikasi_pada) }}.
                                </p>
                            </div>
                        </div>
                    </x-kartu>
                @endif
            </div>
        </div>
    </section>
</x-layouts.publik>
