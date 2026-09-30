@php
    use App\Enums\StatusTopup;
@endphp
<x-layouts.panel judul="Verifikasi Top-up" keterangan="Cocokkan bukti transfer dengan mutasi rekening">
    @section('judul', 'Verifikasi Top-up')

    @php
        $opsi = collect(StatusTopup::cases())
            ->map(fn ($s) => ['label' => $s->label(), 'href' => route('admin.topup.index', ['status' => $s->value]), 'aktif' => $status === $s, 'jumlah' => $jumlah[$s->value] ?? 0])
            ->all();
    @endphp

    <x-pil-saring :opsi="$opsi" class="mb-6" />

    <x-kartu>
        <div class="-mx-5 -my-5 overflow-x-auto">
            <table class="w-full min-w-[40rem] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">Kode</th>
                        <th class="py-3 pr-3 font-semibold">Pengepul</th>
                        <th class="py-3 pr-3 font-semibold">Pengirim</th>
                        <th class="py-3 pr-3 text-right font-semibold">Jumlah</th>
                        <th class="py-3 pr-3 font-semibold">Status</th>
                        <th class="py-3 pr-5 text-right font-semibold">Diajukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($topup as $t)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-3"><a href="{{ route('admin.topup.show', $t) }}" class="font-mono text-xs font-semibold text-merk-700 hover:underline dark:text-merk-400">{{ $t->kode }}</a></td>
                            <td class="py-3 pr-3 font-medium text-slate-900 dark:text-white">{{ $t->pengepul->nama_usaha }}</td>
                            <td class="py-3 pr-3 text-slate-600 dark:text-slate-400">{{ $t->nama_pengirim }}<br><span class="text-xs">{{ $t->bank_pengirim }}</span></td>
                            <td class="py-3 pr-3 text-right font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($t->jumlah) }}</td>
                            <td class="py-3 pr-3"><x-lencana-status :status="$t->status" /></td>
                            <td class="py-3 pr-5 text-right text-xs text-slate-500">{{ tanggal_id($t->created_at) }}<br>{{ $t->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-kosong ikon="dompet" judul="Tidak ada pengajuan" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-6">{{ $topup->links() }}</div>
</x-layouts.panel>
