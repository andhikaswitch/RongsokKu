@php
    use App\Enums\StatusPermintaan;
@endphp
<x-layouts.panel judul="Transaksi" keterangan="Semua permintaan jemput di platform">
    @section('judul', 'Transaksi')

    
    <x-slot:aksi>
        <x-tombol :href="route('admin.transaksi.ekspor', request()->query())" variant="sekunder" ukuran="kecil" ikon="dokumen">Ekspor CSV</x-tombol>
    </x-slot:aksi>

    <x-kartu class="mb-6">
        <form method="GET" action="{{ route('admin.transaksi.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
            <div class="lg:col-span-2">
                <x-kolom nama="q" label="Cari" :nilai="$filter['q'] ?? ''" placeholder="Kode atau nama warga" />
            </div>
            <x-pilihan nama="status" label="Status" kosong="Semua status" :nilai="$filter['status'] ?? ''"
                       :opsi="collect(StatusPermintaan::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" />
            <x-pilihan nama="pengepul" label="Pengepul" kosong="Semua pengepul" :nilai="$filter['pengepul'] ?? ''" :opsi="$pengepul->all()" />
            <x-kolom nama="dari" label="Dari tanggal" tipe="date" :nilai="$filter['dari'] ?? ''" />
            <x-kolom nama="sampai" label="Sampai" tipe="date" :nilai="$filter['sampai'] ?? ''" />
            <div class="flex gap-2 sm:col-span-2 lg:col-span-6 lg:justify-end">
                @if (array_filter($filter))
                    <x-tombol :href="route('admin.transaksi.index')" variant="hantu">Atur ulang</x-tombol>
                @endif
                <x-tombol type="submit" variant="primer" ikon="saring">Terapkan</x-tombol>
            </div>
        </form>
    </x-kartu>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-statistik label="Jumlah transaksi" :nilai="number_format($ringkasan['jumlah'], 0, ',', '.')" ikon="truk" :keterangan="$ringkasan['selesai'].' selesai'" />
        <x-statistik label="Nilai transaksi selesai" :nilai="rupiah($ringkasan['nilai'], true)" ikon="grafik" warna="nilai" />
        <x-statistik label="Komisi platform" :nilai="rupiah($ringkasan['komisi'], true)" ikon="dompet" warna="merk" />
        <x-statistik label="Rongsok terkelola" :nilai="berat($ringkasan['berat'])" ikon="timbangan" warna="sky" />
    </div>

    <x-kartu>
        <div class="-mx-5 -my-5 overflow-x-auto">
            <table class="w-full min-w-[52rem] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">Kode</th>
                        <th class="py-3 pr-3 font-semibold">Warga</th>
                        <th class="py-3 pr-3 font-semibold">Pengepul</th>
                        <th class="py-3 pr-3 font-semibold">Status</th>
                        <th class="py-3 pr-3 text-right font-semibold">Berat</th>
                        <th class="py-3 pr-3 text-right font-semibold">Nilai</th>
                        <th class="py-3 pr-3 text-right font-semibold">Komisi</th>
                        <th class="py-3 pr-5 text-right font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($transaksi as $t)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-3"><a href="{{ route('admin.transaksi.show', $t) }}" class="font-mono text-xs font-semibold text-merk-700 hover:underline dark:text-merk-400">{{ $t->kode }}</a></td>
                            <td class="max-w-[10rem] truncate py-3 pr-3 text-slate-700 dark:text-slate-300">{{ $t->warga?->name }}</td>
                            <td class="max-w-[10rem] truncate py-3 pr-3 text-slate-700 dark:text-slate-300">{{ $t->pengepul?->nama_usaha ?? '— terbuka —' }}</td>
                            <td class="py-3 pr-3"><x-lencana-status :status="$t->status" /></td>
                            <td class="py-3 pr-3 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ berat($t->berat_final_kg ?? $t->estimasi_berat_kg) }}</td>
                            <td class="py-3 pr-3 text-right font-semibold tabular-nums text-slate-900 dark:text-white">{{ rupiah($t->total_final ?? $t->estimasi_total) }}</td>
                            <td class="py-3 pr-3 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ $t->jumlah_komisi ? rupiah($t->jumlah_komisi) : '-' }}</td>
                            <td class="py-3 pr-5 text-right text-xs text-slate-500">{{ tanggal_id($t->created_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-kosong ikon="truk" judul="Tidak ada transaksi" pesan="Coba longgarkan penyaring." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-6">{{ $transaksi->links() }}</div>
</x-layouts.panel>
