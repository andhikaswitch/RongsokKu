<x-layouts.panel judul="Dashboard" :keterangan="$profil->nama_usaha">
    @section('judul', 'Dashboard Pengepul')

    @php
        $saldoMin = (float) pengaturan('saldo_minimum', 10000);
        $saldoCukup = $profil->saldoCukup();
    @endphp

    {{-- ══ PERINGATAN SALDO ══ --}}
    @unless ($saldoCukup)
        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-rose-50 p-5 ring-1 ring-inset ring-rose-600/20
                    dark:bg-rose-500/10 dark:ring-rose-400/30">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                <x-ikon nama="peringatan" ukuran="size-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-rose-900 dark:text-rose-200">Saldo di bawah batas minimum</h2>
                <p class="mt-1 text-sm text-rose-800 dark:text-rose-300">
                    Saldo Anda {{ rupiah($profil->saldo) }}, sedangkan minimum {{ rupiah($saldoMin) }}.
                    Anda tidak dapat menerima permintaan baru sampai saldo diisi.
                </p>
            </div>
            <x-tombol :href="route('pengepul.dompet.index')" variant="bahaya" ikon="dompet">Isi Saldo</x-tombol>
        </div>
    @endunless

    {{-- ══ STATUS VERIFIKASI ══ --}}
    @unless ($profil->terverifikasi())
        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-amber-50 p-5 ring-1 ring-inset ring-amber-600/20
                    dark:bg-amber-500/10 dark:ring-amber-400/30">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                <x-ikon nama="perisai" ukuran="size-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-amber-900 dark:text-amber-200">{{ $profil->status_verifikasi->label() }}</h2>
                <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                    Lapak Anda belum tampil di hasil pencarian warga. Lengkapi berkas verifikasi terlebih dahulu.
                </p>
            </div>
            <x-tombol :href="route('pengepul.verifikasi.form')" variant="primer" ikon="unggah">
                Lengkapi Berkas
            </x-tombol>
        </div>
    @endunless

    {{-- ══ RINGKASAN ══ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-statistik label="Saldo Tersedia" :nilai="rupiah($ringkasan['saldo'])"
                     ikon="dompet" :warna="$saldoCukup ? 'merk' : 'rose'"
                     :keterangan="'minimum '.rupiah($saldoMin)" />
        <x-statistik label="Omzet Bulan Ini" :nilai="rupiah($ringkasan['omzetBulanIni'], true)"
                     ikon="grafik" warna="nilai" />
        <x-statistik label="Komisi Bulan Ini" :nilai="rupiah($ringkasan['komisiBulanIni'])"
                     ikon="petir" warna="violet" :keterangan="$profil->permintaan()->where('status','selesai')->whereMonth('selesai_pada', now()->month)->count().' transaksi'" />
        <x-statistik label="Total Terkumpul" :nilai="berat($ringkasan['berat'])"
                     ikon="timbangan" warna="sky" :keterangan="$ringkasan['transaksi'].' transaksi'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- ══ PERMINTAAN MASUK ══ --}}
            <x-kartu judul="Permintaan Masuk" :keterangan="$masuk->count().' menunggu respons Anda'">
                <x-slot:aksi>
                    <x-tombol :href="route('pengepul.permintaan.index')" variant="hantu" ukuran="kecil" ikon-kanan="panah-kanan">
                        Semua
                    </x-tombol>
                </x-slot:aksi>

                <div class="space-y-3">
                    @forelse ($masuk as $p)
                        <div class="rounded-xl bg-amber-50/60 p-4 ring-1 ring-amber-600/10 dark:bg-amber-500/5 dark:ring-amber-400/20">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <code class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->kode }}</code>
                                        <x-lencana-status :status="$p->status" />
                                    </div>
                                    <p class="mt-1.5 font-semibold text-slate-900 dark:text-white">{{ $p->warga->name }}</p>
                                    <p class="mt-0.5 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                        <x-ikon nama="pin" ukuran="size-3" />
                                        {{ $p->warga->wilayah?->nama }}
                                        @if ($p->jarak_km)
                                            &middot; {{ jarak((float) $p->jarak_km) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900 tabular-nums dark:text-white">{{ rupiah($p->estimasi_total) }}</p>
                                    <p class="text-xs text-slate-400">estimasi {{ berat($p->estimasi_berat_kg) }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($p->item as $item)
                                    <x-lencana warna="slate">
                                        {{ $item->kategori->ikon }} {{ $item->kategori->nama }} · {{ berat($item->estimasi_berat) }}
                                    </x-lencana>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <x-kosong ikon="lonceng" judul="Tidak ada permintaan baru"
                                  pesan="Permintaan dari warga yang memilih lapak Anda akan muncul di sini." />
                    @endforelse
                </div>
            </x-kartu>

            {{-- ══ SEDANG BERJALAN ══ --}}
            @if ($berjalan->isNotEmpty())
                <x-kartu judul="Sedang Diproses">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($berjalan as $p)
                            <div class="flex flex-wrap items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $p->warga->name }}
                                        </span>
                                        <x-lencana-status :status="$p->status" />
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $p->kode }}
                                        @if ($p->jadwal_tanggal)
                                            &middot; jemput {{ tanggal_id($p->jadwal_tanggal) }} ({{ $p->jadwal_sesi }})
                                        @endif
                                    </p>
                                </div>
                                <p class="shrink-0 font-bold text-slate-900 tabular-nums dark:text-white">
                                    {{ rupiah($p->total_final ?? $p->estimasi_total) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </x-kartu>
            @endif

            {{-- ══ PERMINTAAN TERBUKA ══ --}}
            @if ($terbuka->isNotEmpty())
                <x-kartu judul="Permintaan Terbuka" keterangan="Belum diklaim siapa pun — siapa cepat dia dapat">
                    <x-slot:aksi>
                        <x-tombol :href="route('pengepul.terbuka.index')" variant="hantu" ukuran="kecil" ikon-kanan="panah-kanan">
                            Semua
                        </x-tombol>
                    </x-slot:aksi>

                    <div class="space-y-3">
                        @foreach ($terbuka as $p)
                            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-violet-50/60 p-4
                                        ring-1 ring-violet-600/10 dark:bg-violet-500/5 dark:ring-violet-400/20">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400">
                                    <x-ikon nama="petir" ukuran="size-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $p->warga->wilayah?->nama }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ $p->item->count() }} jenis &middot; estimasi {{ berat($p->estimasi_berat_kg) }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                                    {{ rupiah($p->estimasi_total) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </x-kartu>
            @endif
        </div>

        {{-- ══ SISI KANAN ══ --}}
        <div class="space-y-6">
            <x-kartu judul="Omzet 14 Hari">
                @if ($omzetHarian->count() >= 2)
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                        {{ rupiah($omzetHarian->sum(), true) }}
                    </p>
                    <p class="text-xs text-slate-400">total dalam 14 hari terakhir</p>
                    <div class="mt-4">
                        <x-sparkline :data="$omzetHarian->values()->all()" tinggi="h-24" warna="text-nilai-500" />
                    </div>
                @else
                    <p class="text-sm text-slate-400">Data belum cukup untuk menampilkan grafik.</p>
                @endif
            </x-kartu>

            <x-kartu judul="Performa Saya">
                @php $mutu = $profil->mutuKepatuhan(); @endphp

                <div class="space-y-4">
                    <x-meter label="Kepatuhan Harga" :nilai="$profil->skor_kepatuhan_harga" :warna="$mutu['warna']" />
                    <x-meter label="Tingkat Penerimaan" :nilai="$profil->tingkat_penerimaan" warna="sky" />
                    <x-meter label="Ketepatan Waktu" :nilai="$profil->ketepatan_waktu" warna="merk" />
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Rating warga</span>
                    <x-bintang :nilai="$profil->rating_rata" :jumlah="$profil->jumlah_ulasan" ukuran="size-3.5" />
                </div>

                <x-tombol :href="route('pengepul.performa')" variant="sekunder" penuh ukuran="kecil"
                          class="mt-4" ikon-kanan="panah-kanan">
                    Lihat rincian
                </x-tombol>
            </x-kartu>

            <x-kartu judul="Daftar Harga">
                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    Harga yang kompetitif membuat lapak Anda lebih sering dipilih warga.
                    Bandingkan dengan indeks pasar.
                </p>
                <x-tombol :href="route('pengepul.harga.index')" variant="sekunder" penuh class="mt-4" ikon="grafik">
                    Atur Harga
                </x-tombol>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
