<x-layouts.panel judul="Harga Acuan & Indeks" keterangan="Dua sumber harga yang dilihat publik">
    @section('judul', 'Harga Acuan & Indeks')

    <x-slot:aksi>
        @if ($tab === 'acuan')
            <x-tombol :href="route('admin.harga.create')" variant="primer" ukuran="kecil" ikon="tambah">Acuan baru</x-tombol>
        @else
            <form method="POST" action="{{ route('admin.harga.hitung-ulang') }}">
                @csrf
                <x-tombol type="submit" variant="primer" ukuran="kecil" ikon="grafik">Hitung ulang indeks</x-tombol>
            </form>
        @endif
    </x-slot:aksi>

    <x-pil-saring class="mb-6" :opsi="[
        ['label' => 'Harga acuan resmi', 'href' => route('admin.harga.index'), 'aktif' => $tab === 'acuan'],
        ['label' => 'Indeks transaksi hari ini', 'href' => route('admin.harga.index', ['tab' => 'indeks']), 'aktif' => $tab === 'indeks'],
    ]" />

    @if ($tab === 'acuan')
        <p class="mb-4 max-w-3xl text-sm text-slate-600 dark:text-slate-400">
            Harga acuan dicatat dari sumber luar yang bisa ditelusuri — bank sampah induk, pabrik daur ulang, atau survei lapangan.
            Rentang terbaru otomatis menjadi panduan harga pengepul di setiap kategori.
        </p>

        <x-kartu>
            <div class="-mx-5 -my-5 overflow-x-auto">
                <table class="w-full min-w-[46rem] text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">Kategori</th>
                            <th class="py-3 pr-3 text-right font-semibold">Rentang</th>
                            <th class="py-3 pr-3 font-semibold">Sumber</th>
                            <th class="py-3 pr-3 font-semibold">Berlaku</th>
                            <th class="py-3 pr-5 text-right font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($acuan as $a)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $a->kategori->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $a->kategori->induk?->nama }}</p>
                                </td>
                                <td class="py-3 pr-3 text-right font-semibold tabular-nums text-slate-900 dark:text-white">{{ rupiah($a->harga_min) }} – {{ rupiah($a->harga_max) }}</td>
                                <td class="py-3 pr-3">
                                    <p class="text-slate-700 dark:text-slate-300">{{ $a->sumber_nama }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $a->sumber_tipe->label() }}
                                        @if ($a->dokumen_bukti) · <a href="{{ \Illuminate\Support\Facades\Storage::url($a->dokumen_bukti) }}" target="_blank" class="text-merk-700 hover:underline">dokumen</a> @endif
                                    </p>
                                </td>
                                <td class="py-3 pr-3 text-xs text-slate-500">
                                    {{ tanggal_id($a->berlaku_mulai) }}{{ $a->berlaku_sampai ? ' – '.tanggal_id($a->berlaku_sampai) : ' – sekarang' }}
                                </td>
                                <td class="py-3 pr-5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-tombol :href="route('admin.harga.edit', $a)" variant="hantu" ukuran="kecil" ikon="pensil"><span class="sr-only">Ubah</span></x-tombol>
                                        <x-tombol :href="'#hapus-acuan-'.$a->id" variant="hantu" ukuran="kecil" ikon="sampah"><span class="sr-only">Hapus</span></x-tombol>
                                    </div>
                                    <x-modal :id="'hapus-acuan-'.$a->id" judul="Hapus harga acuan ini?">
                                        <form method="POST" action="{{ route('admin.harga.destroy', $a) }}" class="flex justify-end gap-2">
                                            @csrf
                                            @method('DELETE')
                                            <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                                            <x-tombol type="submit" variant="bahaya">Hapus</x-tombol>
                                        </form>
                                    </x-modal>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-kosong ikon="dokumen" judul="Belum ada harga acuan" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-kartu>
        <div class="mt-6">{{ $acuan->links() }}</div>
    @else
        <p class="mb-4 max-w-3xl text-sm text-slate-600 dark:text-slate-400">
            Median harga dari transaksi selesai 30 hari terakhir. Kategori dengan kurang dari
            {{ \App\Models\IndeksHarga::MIN_TRANSAKSI }} transaksi tidak ditampilkan ke publik.
            Terakhir dihitung: {{ $terakhirDihitung ? \Illuminate\Support\Carbon::parse($terakhirDihitung)->diffForHumans() : 'belum pernah' }}.
            Perhitungan otomatis berjalan tiap hari pukul 00.05.
        </p>

        <x-kartu>
            <div class="-mx-5 -my-5 overflow-x-auto">
                <table class="w-full min-w-[44rem] text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-semibold">Kategori</th>
                            <th class="py-3 pr-3 text-right font-semibold">Median</th>
                            <th class="py-3 pr-3 text-right font-semibold">Min – Maks</th>
                            <th class="py-3 pr-3 text-right font-semibold">Transaksi</th>
                            <th class="py-3 pr-3 text-right font-semibold">Perubahan</th>
                            <th class="py-3 pr-5 font-semibold">Publik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($indeks as $i)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $i->kategori->ikon }} {{ $i->kategori->nama }}</td>
                                <td class="py-3 pr-3 text-right font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($i->harga_median) }}</td>
                                <td class="py-3 pr-3 text-right text-xs tabular-nums text-slate-500">{{ rupiah($i->harga_min) }} – {{ rupiah($i->harga_max) }}</td>
                                <td class="py-3 pr-3 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ $i->jumlah_transaksi }}</td>
                                <td @class([
                                    'py-3 pr-3 text-right text-xs font-semibold tabular-nums',
                                    'text-emerald-600' => $i->perubahan_persen > 0,
                                    'text-rose-600' => $i->perubahan_persen < 0,
                                    'text-slate-400' => ! $i->perubahan_persen,
                                ])>{{ \App\Support\Format::persen($i->perubahan_persen !== null ? (float) $i->perubahan_persen : null) }}</td>
                                <td class="py-3 pr-5">
                                    <x-lencana :warna="$i->cukupData() ? 'emerald' : 'slate'">{{ $i->cukupData() ? 'Tampil' : 'Data kurang' }}</x-lencana>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-kosong ikon="grafik" judul="Belum ada indeks hari ini" pesan="Tekan Hitung ulang indeks." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-kartu>
    @endif
</x-layouts.panel>
