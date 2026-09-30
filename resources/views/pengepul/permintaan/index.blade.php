@php
    use App\Enums\StatusPermintaan;
@endphp
<x-layouts.panel judul="Permintaan Masuk" :keterangan="$permintaan->total().' permintaan'">
    @section('judul', 'Permintaan Masuk')

    @php
        $opsi = [['label' => 'Semua', 'href' => route('pengepul.permintaan.index'), 'aktif' => ! $status, 'jumlah' => $jumlahPerStatus->sum()]];
        foreach ([StatusPermintaan::Diajukan, StatusPermintaan::Dijadwalkan, StatusPermintaan::Dijemput, StatusPermintaan::MenungguKonfirmasi, StatusPermintaan::Sengketa, StatusPermintaan::Selesai, StatusPermintaan::Ditolak, StatusPermintaan::Dibatalkan] as $s) {
            if (($jumlahPerStatus[$s->value] ?? 0) > 0 || $status === $s) {
                $opsi[] = ['label' => $s->label(), 'href' => route('pengepul.permintaan.index', ['status' => $s->value]), 'aktif' => $status === $s, 'jumlah' => $jumlahPerStatus[$s->value] ?? 0];
            }
        }
    @endphp

    @unless ($profil->saldoCukup())
        <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-400/30">
            <x-ikon nama="peringatan" ukuran="size-5" class="shrink-0" />
            <span class="flex-1">Saldo di bawah minimum. Anda tidak bisa menerima permintaan baru sampai saldo diisi.</span>
            <x-tombol :href="route('pengepul.dompet.topup')" variant="bahaya" ukuran="kecil">Isi saldo</x-tombol>
        </div>
    @endunless

    <x-pil-saring :opsi="$opsi" class="mb-6" />

    <div class="space-y-3">
        @forelse ($permintaan as $p)
            <a href="{{ route('pengepul.permintaan.show', $p) }}"
               @class([
                   'flex flex-wrap items-center gap-4 rounded-2xl p-4 ring-1 transition hover:shadow-[var(--shadow-naik)] sm:flex-nowrap',
                   'bg-amber-50/70 ring-amber-600/20 dark:bg-amber-500/5 dark:ring-amber-400/20' => $p->status === StatusPermintaan::Diajukan,
                   'bg-white ring-slate-200/80 hover:ring-merk-300 dark:bg-slate-900 dark:ring-slate-800' => $p->status !== StatusPermintaan::Diajukan,
               ])>
                <x-avatar :nama="$p->warga->name" ukuran="size-11" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <code class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->kode }}</code>
                        <x-lencana-status :status="$p->status" />
                        @if ($p->permintaan_terbuka) <x-lencana warna="violet" ikon="petir">Diklaim</x-lencana> @endif
                    </div>
                    <p class="mt-1 truncate font-semibold text-slate-900 dark:text-white">{{ $p->warga->name }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $p->warga->wilayah?->nama }}{{ $p->jarak_km ? ' · '.jarak((float) $p->jarak_km) : '' }}
                        · {{ $p->item->map(fn ($i) => $i->kategori->nama)->join(', ') }}
                    </p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($p->total_final ?? $p->estimasi_total) }}</p>
                    <p class="text-xs text-slate-400">
                        @if ($p->jadwal_tanggal && ! $p->status->selesaiPermanen())
                            {{ tanggal_id($p->jadwal_tanggal) }} · {{ $p->jadwal_sesi }}
                        @else
                            {{ tanggal_id($p->created_at) }}
                        @endif
                    </p>
                </div>
            </a>
        @empty
            <x-kartu>
                <x-kosong ikon="lonceng" judul="Belum ada permintaan"
                          pesan="Permintaan dari warga yang memilih lapak Anda akan muncul di sini. Pastikan daftar harga Anda kompetitif.">
                    <x-tombol :href="route('pengepul.terbuka.index')" variant="sekunder" ikon="petir">Lihat permintaan terbuka</x-tombol>
                </x-kosong>
            </x-kartu>
        @endforelse
    </div>

    <div class="mt-6">{{ $permintaan->links() }}</div>
</x-layouts.panel>
