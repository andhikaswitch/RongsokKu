<x-layouts.panel judul="Sengketa" keterangan="Keberatan warga atas hasil timbangan">
    @section('judul', 'Sengketa')

    <x-pil-saring class="mb-6" :opsi="[
        ['label' => 'Menunggu keputusan', 'href' => route('admin.sengketa.index', ['status' => 'menunggu']), 'aktif' => $status === 'menunggu', 'jumlah' => $jumlah['menunggu'] ?? 0],
        ['label' => 'Sudah diputuskan', 'href' => route('admin.sengketa.index', ['status' => 'selesai']), 'aktif' => $status === 'selesai', 'jumlah' => $jumlah['selesai'] ?? 0],
    ]" />

    <div class="space-y-3">
        @forelse ($sengketa as $s)
            <a href="{{ route('admin.sengketa.show', $s) }}"
               class="flex flex-wrap items-center gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 transition hover:shadow-[var(--shadow-naik)] hover:ring-rose-300 sm:flex-nowrap dark:bg-slate-900 dark:ring-slate-800">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                    <x-ikon nama="peringatan" ukuran="size-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-mono text-xs font-semibold text-slate-500">{{ $s->kode }} · {{ $s->permintaan->kode }}</p>
                    <p class="mt-0.5 truncate font-semibold text-slate-900 dark:text-white">{{ $s->alasan }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $s->pelapor->name }} vs {{ $s->permintaan->pengepul?->nama_usaha }} · {{ $s->created_at->diffForHumans() }}
                    </p>
                </div>
                <p class="shrink-0 font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($s->permintaan->total_final) }}</p>
            </a>
        @empty
            <x-kartu><x-kosong ikon="perisai" judul="Tidak ada sengketa" pesan="Semua transaksi berjalan lancar." /></x-kartu>
        @endforelse
    </div>

    <div class="mt-6">{{ $sengketa->links() }}</div>
</x-layouts.panel>
