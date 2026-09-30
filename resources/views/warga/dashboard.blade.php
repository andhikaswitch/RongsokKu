<x-layouts.panel judul="Dashboard" :keterangan="'Halo, '.auth()->user()->name">
    @section('judul', 'Dashboard Warga')

    <x-slot:aksi>
        <x-tombol :href="route('pengepul.cari')" variant="primer" ukuran="kecil" ikon="cari">
            <span class="hidden sm:inline">Cari Pengepul</span>
            <span class="sm:hidden">Cari</span>
        </x-tombol>
    </x-slot:aksi>

    {{-- ══ RINGKASAN ══ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-statistik label="Total Pendapatan" :nilai="rupiah($ringkasan['pendapatan'])"
                     ikon="dompet" warna="nilai" :keterangan="$ringkasan['transaksi'].' transaksi selesai'" />
        <x-statistik label="Rongsok Terjual" :nilai="berat($ringkasan['berat'])"
                     ikon="timbangan" warna="merk" keterangan="diselamatkan dari TPA" />
        <x-statistik label="CO₂ Dicegah" :nilai="berat($ringkasan['co2'])"
                     ikon="daun" warna="sky" keterangan="estimasi dampak" />
        <x-statistik label="Lencana Diraih" :nilai="$lencanaSaya->count().' / 5'"
                     ikon="piala" warna="violet" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- ══ PERMINTAAN BERJALAN ══ --}}
            <x-kartu judul="Permintaan Berjalan" :keterangan="$berjalan->count().' permintaan sedang diproses'">
                <x-slot:aksi>
                    <x-tombol :href="route('warga.permintaan.index')" variant="hantu" ukuran="kecil" ikon-kanan="panah-kanan">
                        Semua
                    </x-tombol>
                </x-slot:aksi>

                <div class="space-y-3">
                    @forelse ($berjalan as $p)
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <code class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $p->kode }}
                                        </code>
                                        <x-lencana-status :status="$p->status" />
                                        @if ($p->permintaan_terbuka)
                                            <x-lencana warna="violet" ikon="petir">Terbuka</x-lencana>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 font-semibold text-slate-900 dark:text-white">
                                        {{ $p->pengepul?->nama_usaha ?? 'Menunggu pengepul mengklaim' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $p->item->count() }} jenis &middot; estimasi {{ berat($p->estimasi_berat_kg) }}
                                        @if ($p->jadwal_tanggal)
                                            &middot; jemput {{ tanggal_id($p->jadwal_tanggal) }}
                                        @endif
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-slate-900 tabular-nums dark:text-white">
                                        {{ rupiah($p->total_final ?? $p->estimasi_total) }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ $p->total_final ? 'hasil timbangan' : 'estimasi' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Penanda progres, murni CSS --}}
                            @if ($p->status->langkah() > 0)
                                <div class="mt-4 flex items-center gap-1">
                                    @for ($l = 1; $l <= 5; $l++)
                                        <div @class([
                                            'h-1.5 flex-1 rounded-full',
                                            'bg-merk-500' => $l <= $p->status->langkah(),
                                            'bg-slate-200 dark:bg-slate-700' => $l > $p->status->langkah(),
                                        ])></div>
                                    @endfor
                                </div>
                                <div class="mt-1.5 flex justify-between text-[10px] text-slate-400">
                                    <span>Diajukan</span>
                                    <span>Dijadwalkan</span>
                                    <span>Dijemput</span>
                                    <span>Ditimbang</span>
                                    <span>Selesai</span>
                                </div>
                            @endif

                            @if ($p->status === \App\Enums\StatusPermintaan::MenungguKonfirmasi)
                                <div class="mt-4 flex items-start gap-2 rounded-lg bg-violet-50 p-3 dark:bg-violet-500/10">
                                    <x-ikon nama="peringatan" ukuran="size-4" class="mt-0.5 shrink-0 text-violet-600 dark:text-violet-400" />
                                    <p class="text-xs leading-relaxed text-violet-800 dark:text-violet-300">
                                        Pengepul sudah menimbang barangmu. Periksa hasilnya, lalu konfirmasi
                                        bila sudah sesuai.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <x-kosong ikon="truk" judul="Belum ada permintaan berjalan"
                                  pesan="Punya rongsok menumpuk? Cari pengepul terdekat dan ajukan penjemputan.">
                            <x-tombol :href="route('pengepul.cari')" variant="primer" ikon="cari">
                                Cari Pengepul
                            </x-tombol>
                        </x-kosong>
                    @endforelse
                </div>
            </x-kartu>

            {{-- ══ RIWAYAT ══ --}}
            @if ($riwayat->isNotEmpty())
                <x-kartu judul="Transaksi Terakhir">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($riwayat as $r)
                            <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    <x-ikon nama="cek-lingkar" ukuran="size-5" />
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $r->pengepul?->nama_usaha ?? '-' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ tanggal_id($r->selesai_pada) }} &middot; {{ berat($r->berat_final_kg) }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="font-bold text-slate-900 tabular-nums dark:text-white">{{ rupiah($r->total_final) }}</p>
                                    @if (! $r->ulasan)
                                        <span class="text-xs font-medium text-nilai-600 dark:text-nilai-400">Belum diulas</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-kartu>
            @endif
        </div>

        {{-- ══ SISI KANAN ══ --}}
        <div class="space-y-6">
            <x-kartu judul="Dampak Lingkunganmu">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                            <x-ikon nama="daur-ulang" ukuran="size-5" />
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ berat($ringkasan['berat']) }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">diselamatkan dari TPA</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                            <x-ikon nama="daun" ukuran="size-5" />
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ berat($ringkasan['co2']) }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">CO₂ dicegah</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-nilai-50 text-nilai-600 dark:bg-nilai-500/10 dark:text-nilai-400">
                            <x-ikon nama="daun" ukuran="size-5" />
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ number_format($ringkasan['co2'] / 22, 1, ',', '.') }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">setara pohon per tahun</p>
                        </div>
                    </div>
                </div>

                <x-tombol :href="route('warga.dampak')" variant="sekunder" penuh ukuran="kecil" class="mt-5" ikon-kanan="panah-kanan">
                    Lihat rincian
                </x-tombol>
            </x-kartu>

            {{-- Lencana --}}
            <x-kartu judul="Lencana Saya">
                @if ($lencanaSaya->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($lencanaSaya as $l)
                            <span class="grid size-12 place-items-center rounded-2xl bg-slate-100 text-2xl dark:bg-slate-800"
                                  title="{{ $l->nama }} — {{ $l->deskripsi }}">
                                {{ $l->ikon }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Belum ada lencana. Selesaikan transaksi pertamamu untuk meraih lencana Pemula Hijau.
                    </p>
                @endif

                @if ($lencanaBerikut)
                    @php
                        $sisa = max(0, (float) $lencanaBerikut->syarat_berat_kg - $ringkasan['berat']);
                    @endphp
                    <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <x-meter :label="$lencanaBerikut->ikon.' '.$lencanaBerikut->nama"
                                 :nilai="$ringkasan['berat']"
                                 :maks="(float) $lencanaBerikut->syarat_berat_kg"
                                 warna="merk"
                                 satuan=" kg"
                                 :keterangan="'Kurang '.berat($sisa).' lagi'" />
                    </div>
                @endif
            </x-kartu>

            <x-kartu judul="Harga Hari Ini">
                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    Cek harga pasar sebelum menjual, supaya kamu tahu penawaran mana yang wajar.
                </p>
                <x-tombol :href="route('harga.index')" variant="sekunder" penuh class="mt-4" ikon="grafik">
                    Buka Pusat Harga
                </x-tombol>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
