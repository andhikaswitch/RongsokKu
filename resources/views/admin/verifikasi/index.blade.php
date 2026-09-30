@php
    use App\Enums\StatusVerifikasi;
@endphp
<x-layouts.panel judul="Verifikasi Pengepul" keterangan="Periksa identitas sebelum lapak tampil ke publik">
    @section('judul', 'Verifikasi Pengepul')

    @php
        $opsi = collect([StatusVerifikasi::Menunggu, StatusVerifikasi::Terverifikasi, StatusVerifikasi::Ditolak, StatusVerifikasi::Draf])
            ->map(fn ($s) => ['label' => $s->label(), 'href' => route('admin.verifikasi.index', ['status' => $s->value]), 'aktif' => $status === $s, 'jumlah' => $jumlah[$s->value] ?? 0])
            ->all();
    @endphp

    <x-pil-saring :opsi="$opsi" class="mb-6" />

    <x-kartu>
        <div class="-mx-5 -my-5 overflow-x-auto">
            <table class="w-full min-w-[40rem] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">Lapak</th>
                        <th class="py-3 pr-3 font-semibold">Pemilik</th>
                        <th class="py-3 pr-3 font-semibold">Wilayah</th>
                        <th class="py-3 pr-3 font-semibold">Status</th>
                        <th class="py-3 pr-5 text-right font-semibold">Diperbarui</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($pengepul as $p)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.verifikasi.show', $p) }}" class="font-semibold text-slate-900 hover:text-merk-700 dark:text-white">
                                    {{ $p->nama_usaha }}
                                </a>
                                @if ($p->izin_b3) <x-lencana warna="violet" class="ml-1">B3</x-lencana> @endif
                            </td>
                            <td class="py-3 pr-3 text-slate-600 dark:text-slate-400">{{ $p->user->name }}<br><span class="text-xs">{{ $p->user->telepon }}</span></td>
                            <td class="py-3 pr-3 text-slate-600 dark:text-slate-400">{{ $p->user->wilayah?->nama ?? '-' }}</td>
                            <td class="py-3 pr-3"><x-lencana-status :status="$p->status_verifikasi" /></td>
                            <td class="py-3 pr-5 text-right text-xs text-slate-500">{{ tanggal_id($p->updated_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-kosong ikon="perisai" judul="Tidak ada pengajuan" pesan="Tidak ada pengepul dengan status ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-6">{{ $pengepul->links() }}</div>
</x-layouts.panel>
