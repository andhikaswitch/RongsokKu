<x-layouts.panel judul="Dampak & Lencana" keterangan="Kontribusimu untuk lingkungan">
    @section('judul', 'Dampak & Lencana')

    <div class="grid gap-4 sm:grid-cols-3">
        <x-statistik label="Total Didaur Ulang" :nilai="berat($berat)" ikon="daur-ulang" warna="merk" />
        <x-statistik label="CO₂ Dicegah" :nilai="berat($co2)" ikon="daun" warna="sky" />
        <x-statistik label="Setara Pohon" :nilai="number_format($co2 / 22, 1, ',', '.').' pohon'"
                     ikon="daun" warna="violet" keterangan="serapan per tahun" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-kartu judul="Rincian per Kategori">
            @php $maks = max(1, (float) ($perKategori->max('berat') ?? 1)); @endphp

            <div class="space-y-4">
                @forelse ($perKategori as $k)
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="truncate text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ $k->ikon }} {{ $k->nama }}
                            </span>
                            <span class="shrink-0 text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                                {{ berat($k->berat) }}
                            </span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-merk-500"
                                 style="width: {{ round((float) $k->berat / $maks * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ rupiah($k->nilai) }} diterima</p>
                    </div>
                @empty
                    <x-kosong ikon="kotak" judul="Belum ada data"
                              pesan="Rincian muncul setelah transaksi pertamamu selesai." />
                @endforelse
            </div>
        </x-kartu>

        <x-kartu judul="Koleksi Lencana" :keterangan="count($lencanaSaya).' dari '.$semuaLencana->count().' lencana diraih'">
            <div class="space-y-3">
                @foreach ($semuaLencana as $l)
                    @php $diraih = in_array($l->id, $lencanaSaya, true); @endphp

                    <div @class([
                        'flex items-center gap-4 rounded-xl p-3 transition',
                        'bg-merk-50 dark:bg-merk-500/10' => $diraih,
                        'bg-slate-50 opacity-60 dark:bg-slate-800/50' => ! $diraih,
                    ])>
                        <span @class([
                            'grid size-12 shrink-0 place-items-center rounded-2xl text-2xl',
                            'bg-white shadow-sm dark:bg-slate-800' => $diraih,
                            'bg-slate-200 grayscale dark:bg-slate-700' => ! $diraih,
                        ])>
                            {{ $l->ikon }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $l->nama }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $l->deskripsi }}</p>
                        </div>

                        @if ($diraih)
                            <x-ikon nama="cek-lingkar" ukuran="size-5" class="shrink-0 text-merk-600 dark:text-merk-400" />
                        @else
                            <span class="shrink-0 text-xs font-medium text-slate-400 tabular-nums">
                                {{ berat($l->syarat_berat_kg) }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-kartu>
    </div>

    <x-kartu judul="Bagaimana dampak ini dihitung?" class="mt-6">
        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
            Setiap kategori rongsok punya faktor emisi tersendiri, yaitu perkiraan berapa kilogram CO₂
            yang tidak jadi dilepas ke udara ketika satu kilogram material didaur ulang alih-alih
            diproduksi baru atau berakhir di tempat pembuangan. Angka pada halaman ini adalah
            penjumlahan berat aktual hasil timbangan dikali faktor emisi tiap kategori.
            Perbandingan dengan pohon memakai asumsi umum bahwa satu pohon dewasa menyerap
            sekitar 22 kg CO₂ per tahun. Semua angka di sini bersifat estimasi.
        </p>
    </x-kartu>
</x-layouts.panel>
