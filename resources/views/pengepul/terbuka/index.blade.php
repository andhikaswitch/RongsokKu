<x-layouts.panel judul="Permintaan Terbuka" :keterangan="'Dalam radius '.$profil->radius_layanan_km.' km dari lapak Anda'">
    @section('judul', 'Permintaan Terbuka')

    <div class="mb-6 rounded-2xl bg-violet-50 p-5 text-sm leading-relaxed text-violet-900 ring-1 ring-inset ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-100 dark:ring-violet-400/30">
        <p class="font-bold">Siapa cepat dia dapat.</p>
        <p class="mt-1">
            Warga ini belum memilih pengepul. Saat Anda mengklaim, harga dari daftar harga Anda saat itu yang
            dikunci untuk permintaan tersebut. Anda hanya bisa mengklaim bila menerima semua jenis barang di dalamnya.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($permintaan as $p)
            <x-kartu padat>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <code class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->kode }}</code>
                        <p class="mt-1 truncate font-semibold text-slate-900 dark:text-white">{{ $p->warga->wilayah?->namaLengkap() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $p->jarak_saya !== null ? jarak((float) $p->jarak_saya).' dari lapak · ' : '' }}diajukan {{ $p->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <p class="shrink-0 text-lg font-extrabold tabular-nums text-slate-900 dark:text-white">{{ berat($p->estimasi_berat_kg) }}</p>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach ($p->item as $item)
                        <x-lencana :warna="$item->kategori->limbah_b3 ? 'rose' : 'slate'">
                            {{ $item->kategori->ikon }} {{ $item->kategori->nama }} · {{ berat($item->estimasi_berat) }}
                        </x-lencana>
                    @endforeach
                </div>

                <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                    Jadwal diusulkan: {{ tanggal_id($p->jadwal_tanggal) }}, sesi {{ $p->jadwal_sesi }}
                </p>

                <x-slot:kaki>
                    @if ($p->bisa_diklaim)
                        <form method="POST" action="{{ route('pengepul.terbuka.klaim', $p) }}">
                            @csrf
                            <x-tombol type="submit" variant="primer" penuh ikon="petir">Klaim permintaan ini</x-tombol>
                        </form>
                    @else
                        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                            Ada jenis barang yang tidak ada di daftar harga Anda.
                            <a href="{{ route('pengepul.harga.index') }}" class="font-semibold text-merk-700 hover:underline dark:text-merk-400">Atur harga</a>
                        </p>
                    @endif
                </x-slot:kaki>
            </x-kartu>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-kartu>
                    <x-kosong ikon="petir" judul="Tidak ada permintaan terbuka"
                              pesan="Belum ada warga di sekitar lapak Anda yang membuat permintaan terbuka. Coba perluas radius layanan di profil lapak." />
                </x-kartu>
            </div>
        @endforelse
    </div>
</x-layouts.panel>
