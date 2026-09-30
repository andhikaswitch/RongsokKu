@php
    use App\Enums\StatusPermintaan;
@endphp
<x-layouts.panel judul="Permintaan Saya" :keterangan="$permintaan->total().' permintaan'">
    @section('judul', 'Permintaan Saya')

    <x-slot:aksi>
        <x-tombol :href="route('warga.ajukan')" variant="primer" ukuran="kecil" ikon="tambah">Ajukan</x-tombol>
    </x-slot:aksi>

    @php
        $opsi = [['label' => 'Semua', 'href' => route('warga.permintaan.index'), 'aktif' => ! $status, 'jumlah' => $jumlahPerStatus->sum()]];
        foreach ([StatusPermintaan::MenungguKonfirmasi, StatusPermintaan::Diajukan, StatusPermintaan::Dijadwalkan, StatusPermintaan::Dijemput, StatusPermintaan::Selesai, StatusPermintaan::Sengketa, StatusPermintaan::Dibatalkan, StatusPermintaan::Ditolak] as $s) {
            if (($jumlahPerStatus[$s->value] ?? 0) > 0 || $status === $s) {
                $opsi[] = ['label' => $s->label(), 'href' => route('warga.permintaan.index', ['status' => $s->value]), 'aktif' => $status === $s, 'jumlah' => $jumlahPerStatus[$s->value] ?? 0];
            }
        }
    @endphp

    <x-pil-saring :opsi="$opsi" class="mb-6" />

    <div class="space-y-3">
        @forelse ($permintaan as $p)
            <a href="{{ route('warga.permintaan.show', $p) }}"
               class="flex flex-wrap items-center gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 transition hover:shadow-[var(--shadow-naik)] hover:ring-merk-300 sm:flex-nowrap dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-merk-700">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl dark:bg-slate-800">
                    {{ $p->item->first()?->kategori->ikon ?? '♻️' }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <code class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->kode }}</code>
                        <x-lencana-status :status="$p->status" />
                        @if ($p->permintaan_terbuka && ! $p->profil_pengepul_id)
                            <x-lencana warna="violet" ikon="petir">Terbuka</x-lencana>
                        @endif
                    </div>
                    <p class="mt-1 truncate font-semibold text-slate-900 dark:text-white">
                        {{ $p->pengepul?->nama_usaha ?? 'Menunggu pengepul mengklaim' }}
                    </p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $p->item->map(fn ($i) => $i->kategori->nama)->join(', ') }}
                        · {{ tanggal_id($p->created_at) }}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($p->total_final ?? $p->estimasi_total) }}</p>
                    <p class="text-xs text-slate-400">{{ berat($p->berat_final_kg ?? $p->estimasi_berat_kg) }}</p>
                    @if ($p->status === StatusPermintaan::MenungguKonfirmasi)
                        <p class="mt-1 text-xs font-bold text-violet-600 dark:text-violet-400">Perlu konfirmasi →</p>
                    @elseif ($p->bisaDiulas())
                        <p class="mt-1 text-xs font-semibold text-nilai-600 dark:text-nilai-400">Beri ulasan →</p>
                    @endif
                </div>
            </a>
        @empty
            <x-kartu>
                <x-kosong ikon="truk" judul="Belum ada permintaan" pesan="Ajukan penjemputan pertamamu dan biarkan pengepul yang datang.">
                    <x-tombol :href="route('warga.ajukan')" variant="primer" ikon="tambah">Ajukan Penjemputan</x-tombol>
                </x-kosong>
            </x-kartu>
        @endforelse
    </div>

    <div class="mt-6">{{ $permintaan->links() }}</div>
</x-layouts.panel>
