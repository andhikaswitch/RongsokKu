<x-layouts.panel judul="Dashboard Admin" keterangan="Ringkasan operasional platform">
    @section('judul', 'Dashboard Admin')

    {{-- ══ ANTREAN YANG BUTUH TINDAKAN ══ --}}
    @if (array_sum($antrean) > 0)
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['verifikasi', 'Pengepul menunggu verifikasi', 'perisai', 'admin.verifikasi.index', 'amber'],
                ['topup', 'Top-up menunggu konfirmasi', 'dompet', 'admin.topup.index', 'sky'],
                ['sengketa', 'Sengketa belum ditangani', 'peringatan', 'admin.sengketa.index', 'rose'],
            ] as [$kunci, $label, $ikon, $rute, $warna])
                @if ($antrean[$kunci] > 0)
                    <a href="{{ route($rute) }}"
                       @class([
                           'flex items-center gap-4 rounded-2xl p-5 ring-1 ring-inset transition hover:shadow-md',
                           'bg-amber-50 ring-amber-600/20 dark:bg-amber-500/10 dark:ring-amber-400/30' => $warna === 'amber',
                           'bg-sky-50 ring-sky-600/20 dark:bg-sky-500/10 dark:ring-sky-400/30' => $warna === 'sky',
                           'bg-rose-50 ring-rose-600/20 dark:bg-rose-500/10 dark:ring-rose-400/30' => $warna === 'rose',
                       ])>
                        <span @class([
                            'grid size-11 shrink-0 place-items-center rounded-xl',
                            'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400' => $warna === 'amber',
                            'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400' => $warna === 'sky',
                            'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400' => $warna === 'rose',
                        ])>
                            <x-ikon :nama="$ikon" ukuran="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">{{ $antrean[$kunci] }}</p>
                            <p class="text-xs leading-snug text-slate-600 dark:text-slate-400">{{ $label }}</p>
                        </div>
                        <x-ikon nama="panah-kanan" ukuran="size-4" class="ml-auto shrink-0 text-slate-400" />
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ══ KPI ══ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-statistik label="Pendapatan Komisi" :nilai="rupiah($kpi['pendapatan'], true)"
                     ikon="dompet" warna="merk"
                     :keterangan="'bulan ini '.rupiah($kpi['pendapatanBulanIni'], true)" />
        <x-statistik label="Nilai Transaksi (GMV)" :nilai="rupiah($kpi['gmv'], true)"
                     ikon="grafik" warna="nilai" :keterangan="$kpi['transaksi'].' transaksi'" />
        <x-statistik label="Rongsok Terkelola" :nilai="berat($kpi['berat'])"
                     ikon="timbangan" warna="sky" />
        <x-statistik label="Pengguna Aktif" :nilai="number_format($kpi['warga'] + $kpi['pengepul'], 0, ',', '.')"
                     ikon="pengguna-grup" warna="violet"
                     :keterangan="$kpi['warga'].' warga · '.$kpi['pengepul'].' pengepul'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- ══ GRAFIK KOMISI ══ --}}
            <x-kartu judul="Pendapatan Komisi" keterangan="30 hari terakhir">
                @if ($komisiHarian->count() >= 2)
                    <p class="text-3xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                        {{ rupiah($komisiHarian->sum()) }}
                    </p>
                    <p class="text-xs text-slate-400">
                        rata-rata {{ rupiah($komisiHarian->avg()) }} per hari
                    </p>
                    <div class="mt-5">
                        <x-sparkline :data="$komisiHarian->values()->all()" tinggi="h-32" />
                    </div>
                @else
                    <x-kosong ikon="grafik" judul="Data belum cukup"
                              pesan="Grafik muncul setelah ada transaksi di beberapa hari berbeda." />
                @endif
            </x-kartu>

            {{-- ══ PENGEPUL PERLU DIAWASI ══ --}}
            @if ($perluDiawasi->isNotEmpty())
                <x-kartu judul="Pengepul Perlu Diawasi"
                         keterangan="Skor kepatuhan harga di bawah 85%">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($perluDiawasi as $p)
                            @php $mutu = $p->mutuKepatuhan(); @endphp
                            <a href="{{ route('pengepul.detail', $p) }}"
                               class="flex items-center gap-4 py-3 first:pt-0 last:pb-0 transition hover:opacity-75">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                                    <x-ikon nama="peringatan" ukuran="size-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $p->nama_usaha }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $p->total_transaksi }} transaksi &middot; rating {{ number_format((float) $p->rating_rata, 1, ',', '.') }}
                                    </p>
                                </div>
                                <x-lencana :warna="$mutu['warna']">
                                    {{ rtrim(rtrim(number_format((float) $p->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',') }}%
                                </x-lencana>
                            </a>
                        @endforeach
                    </div>

                    <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        Skor rendah berarti pengepul sering membayar di bawah harga yang dipajangnya sendiri.
                        Periksa riwayat transaksinya sebelum menjatuhkan sanksi.
                    </p>
                </x-kartu>
            @endif

            {{-- ══ TRANSAKSI TERBARU ══ --}}
            <x-kartu judul="Transaksi Terbaru">
                <x-slot:aksi>
                    <x-tombol :href="route('admin.transaksi.index')" variant="hantu" ukuran="kecil" ikon-kanan="panah-kanan">
                        Semua
                    </x-tombol>
                </x-slot:aksi>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400 dark:border-slate-800">
                                <th class="pb-2 pr-3 font-semibold">Kode</th>
                                <th class="pb-2 pr-3 font-semibold">Warga</th>
                                <th class="pb-2 pr-3 font-semibold">Pengepul</th>
                                <th class="pb-2 pr-3 font-semibold">Status</th>
                                <th class="pb-2 text-right font-semibold">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($transaksiTerbaru as $t)
                                <tr>
                                    <td class="py-2.5 pr-3">
                                        <code class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $t->kode }}</code>
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <span class="block max-w-[8rem] truncate text-slate-700 dark:text-slate-300">
                                            {{ $t->warga->name }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <span class="block max-w-[9rem] truncate text-slate-700 dark:text-slate-300">
                                            {{ $t->pengepul?->nama_usaha ?? '— terbuka —' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <x-lencana-status :status="$t->status" />
                                    </td>
                                    <td class="py-2.5 text-right font-semibold text-slate-900 tabular-nums dark:text-white">
                                        {{ rupiah($t->total_final ?? $t->estimasi_total) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-kartu>
        </div>

        {{-- ══ SISI KANAN ══ --}}
        <div class="space-y-6">
            <x-kartu judul="Sebaran Status Transaksi">
                @php $totalTrx = max(1, $statusTransaksi->sum()); @endphp

                <div class="space-y-2.5">
                    @foreach (\App\Enums\StatusPermintaan::cases() as $s)
                        @php $jml = $statusTransaksi[$s->value] ?? 0; @endphp
                        @if ($jml > 0)
                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="truncate text-sm text-slate-600 dark:text-slate-400">{{ $s->label() }}</span>
                                    <span class="shrink-0 text-sm font-bold text-slate-900 tabular-nums dark:text-white">{{ $jml }}</span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-merk-500" style="width: {{ round($jml / $totalTrx * 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-kartu>

            <x-kartu judul="Pengepul Teratas">
                <div class="space-y-3">
                    @foreach ($pengepulTeratas as $i => $p)
                        <div class="flex items-center gap-3">
                            <span class="w-5 shrink-0 text-center text-sm font-bold text-slate-400 tabular-nums">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $p->nama_usaha }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $p->total_transaksi }} transaksi &middot; {{ berat($p->total_berat_kg) }}
                                </p>
                            </div>
                            <x-bintang :nilai="$p->rating_rata" ukuran="size-3" :tampil-angka="true" />
                        </div>
                    @endforeach
                </div>
            </x-kartu>

            <x-kartu judul="Aksi Cepat">
                <div class="space-y-2">
                    @foreach ([
                        ['admin.harga.index', 'grafik', 'Kelola Harga Acuan'],
                        ['admin.kategori.index', 'kotak', 'Kategori Sampah'],
                        ['admin.pengguna.index', 'pengguna-grup', 'Kelola Pengguna'],
                        ['admin.pengaturan', 'pengaturan', 'Pengaturan Platform'],
                    ] as [$rute, $ikon, $label])
                        <a href="{{ route($rute) }}"
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600
                                  transition hover:bg-slate-50 hover:text-slate-900
                                  dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">
                            <x-ikon :nama="$ikon" ukuran="size-4.5" />
                            {{ $label }}
                            <x-ikon nama="panah-kanan" ukuran="size-3.5" class="ml-auto text-slate-300" />
                        </a>
                    @endforeach
                </div>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
