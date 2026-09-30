<x-layouts.panel :judul="'Transaksi '.$permintaan->kode" :keterangan="$permintaan->status->label()">
    @section('judul', 'Transaksi '.$permintaan->kode)

    @php $p = $permintaan; $selisih = $p->selisihHargaPersen(); @endphp

    <x-slot:aksi>
        <x-tombol :href="route('admin.transaksi.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Semua</x-tombol>
    </x-slot:aksi>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-kartu judul="Rincian barang">
                @include('partials.permintaan.rincian-barang', ['permintaan' => $p])
            </x-kartu>

            @if ($p->sengketa)
                <x-kartu :judul="'Sengketa '.$p->sengketa->kode">
                    <p class="text-sm text-slate-600 dark:text-slate-400"><strong>{{ $p->sengketa->alasan }}</strong> — {{ $p->sengketa->deskripsi }}</p>
                    <x-tombol :href="route('admin.sengketa.show', $p->sengketa)" variant="sekunder" ukuran="kecil" class="mt-3" ikon-kanan="panah-kanan">Buka sengketa</x-tombol>
                </x-kartu>
            @endif

            <x-kartu judul="Mutasi saldo terkait" keterangan="Dari buku besar pengepul">
                @forelse ($mutasi as $m)
                    <div class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span class="text-slate-600 dark:text-slate-400">{{ $m->keterangan }}</span>
                        <span class="font-bold tabular-nums text-rose-600">{{ rupiah($m->jumlah) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada komisi — komisi hanya dipotong saat transaksi selesai.</p>
                @endforelse
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Status">
                @include('partials.permintaan.linimasa', ['permintaan' => $p])
            </x-kartu>

            <x-kartu judul="Pihak">
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Warga"><a href="{{ route('admin.pengguna.show', $p->warga) }}" class="hover:underline">{{ $p->warga->name }}</a></x-baris-info>
                    <x-baris-info label="Pengepul">
                        @if ($p->pengepul)
                            <a href="{{ route('admin.pengguna.show', $p->pengepul->user) }}" class="hover:underline">{{ $p->pengepul->nama_usaha }}</a>
                        @else
                            — terbuka —
                        @endif
                    </x-baris-info>
                    <x-baris-info label="Alamat">{{ $p->alamat_jemput }}</x-baris-info>
                    <x-baris-info label="Jarak">{{ $p->jarak_km ? jarak((float) $p->jarak_km) : '-' }}</x-baris-info>
                </dl>
            </x-kartu>

            <x-kartu judul="Keuangan">
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Estimasi">{{ rupiah($p->estimasi_total) }}</x-baris-info>
                    <x-baris-info label="Dibayar ke warga">{{ $p->total_final !== null ? rupiah($p->total_final) : '-' }}</x-baris-info>
                    <x-baris-info label="Tarif komisi">{{ $p->tarif_komisi !== null ? rtrim(rtrim(number_format((float) $p->tarif_komisi, 2, ',', '.'), '0'), ',').'%' : '-' }}</x-baris-info>
                    <x-baris-info label="Komisi platform">{{ $p->jumlah_komisi !== null ? rupiah($p->jumlah_komisi) : '-' }}</x-baris-info>
                    @if ($selisih !== null)
                        <x-baris-info label="Selisih harga vs kesepakatan">
                            <span @class(['font-bold', 'text-rose-600' => $selisih < -0.5, 'text-emerald-600' => $selisih >= -0.5])>
                                {{ \App\Support\Format::persen($selisih) }}
                            </span>
                        </x-baris-info>
                    @endif
                </dl>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
