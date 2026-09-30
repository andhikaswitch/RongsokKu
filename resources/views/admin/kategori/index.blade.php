<x-layouts.panel judul="Kategori Sampah" keterangan="Golongan, turunan, harga acuan, dan faktor dampak">
    @section('judul', 'Kategori Sampah')

    <x-slot:aksi>
        <x-tombol :href="route('admin.kategori.create')" variant="primer" ukuran="kecil" ikon="tambah">Kategori baru</x-tombol>
    </x-slot:aksi>

    <div class="space-y-6">
        @foreach ($golongan as $g)
            <x-kartu :judul="$g->ikon.' '.$g->nama" :keterangan="$g->anak->count().' turunan · CO₂ '.rtrim(rtrim(number_format((float) $g->faktor_co2_per_kg, 3, ',', '.'), '0'), ',').' kg/kg'">
                <x-slot:aksi>
                    <div class="flex gap-1">
                        <x-tombol :href="route('admin.kategori.create', ['induk' => $g->id])" variant="hantu" ukuran="kecil" ikon="tambah">Turunan</x-tombol>
                        <x-tombol :href="route('admin.kategori.edit', $g)" variant="hantu" ukuran="kecil" ikon="pensil">Ubah</x-tombol>
                    </div>
                </x-slot:aksi>

                <div class="-mx-5 -my-5 overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400 dark:border-slate-800">
                                <th class="px-5 py-2.5 font-semibold">Nama</th>
                                <th class="py-2.5 pr-3 text-right font-semibold">Acuan</th>
                                <th class="py-2.5 pr-3 text-right font-semibold">Pengepul</th>
                                <th class="py-2.5 pr-3 font-semibold">Status</th>
                                <th class="py-2.5 pr-5 text-right font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($g->anak as $k)
                                <tr @class(['opacity-60' => ! $k->aktif])>
                                    <td class="px-5 py-3">
                                        <span class="font-semibold text-slate-900 dark:text-white">{{ $k->nama }}</span>
                                        @if ($k->limbah_b3) <x-lencana warna="rose" class="ml-1">B3</x-lencana> @endif
                                        <p class="text-xs text-slate-400">per {{ $k->satuan }}</p>
                                    </td>
                                    <td class="py-3 pr-3 text-right text-xs tabular-nums text-slate-600 dark:text-slate-400">
                                        {{ $k->harga_acuan_min ? rupiah($k->harga_acuan_min).' – '.rupiah($k->harga_acuan_max) : '-' }}
                                    </td>
                                    <td class="py-3 pr-3 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ $k->harga_pengepul_count }}</td>
                                    <td class="py-3 pr-3">
                                        <x-lencana :warna="$k->aktif ? 'emerald' : 'slate'">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</x-lencana>
                                    </td>
                                    <td class="py-3 pr-5 text-right">
                                        <div class="flex justify-end gap-1">
                                            <x-tombol :href="route('admin.kategori.edit', $k)" variant="hantu" ukuran="kecil" ikon="pensil"><span class="sr-only">Ubah</span></x-tombol>
                                            <x-tombol :href="'#hapus-'.$k->id" variant="hantu" ukuran="kecil" ikon="sampah"><span class="sr-only">Hapus</span></x-tombol>
                                        </div>
                                        <x-modal :id="'hapus-'.$k->id" :judul="'Hapus '.$k->nama.'?'"
                                                 keterangan="Bila sudah pernah dipakai transaksi, kategori hanya dinonaktifkan.">
                                            <form method="POST" action="{{ route('admin.kategori.destroy', $k) }}" class="flex justify-end gap-2">
                                                @csrf
                                                @method('DELETE')
                                                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                                                <x-tombol type="submit" variant="bahaya">Hapus</x-tombol>
                                            </form>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-6 text-center text-sm text-slate-400">Belum ada turunan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-kartu>
        @endforeach
    </div>
</x-layouts.panel>
