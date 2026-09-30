<x-layouts.panel judul="Ajukan Penjemputan" keterangan="Langkah 2 dari 3 · Tambahkan barang">
    @section('judul', 'Tambah Barang')

    <x-langkah :aktif="2" class="mb-8 max-w-2xl" />

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            {{-- Penerima --}}
            <x-kartu padat>
                <div class="flex items-center gap-4">
                    @if ($terbuka)
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                            <x-ikon nama="petir" ukuran="size-6" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 dark:text-white">Permintaan terbuka</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Harga di bawah ini perkiraan dari indeks pasar.</p>
                        </div>
                    @else
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                            <x-ikon nama="toko" ukuran="size-6" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-slate-900 dark:text-white">{{ $pengepul->nama_usaha }}</p>
                            <p class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                <x-bintang :nilai="$pengepul->rating_rata" ukuran="size-3.5" />
                                <span>· patuh harga {{ rtrim(rtrim(number_format((float) $pengepul->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',') }}%</span>
                            </p>
                        </div>
                    @endif
                    <x-tombol :href="route('warga.ajukan')" variant="hantu" ukuran="kecil">Ganti</x-tombol>
                </div>
            </x-kartu>

            {{-- Form tambah barang --}}
            <x-kartu judul="Tambah barang" keterangan="Tambahkan satu per satu. Jenis yang sama akan digabung.">
                @if ($kategori->isEmpty())
                    <x-kosong ikon="kotak" judul="Tidak ada kategori tersedia"
                              pesan="Pengepul ini sedang tidak menerima kategori apa pun. Coba pengepul lain." />
                @else
                    <form method="POST" action="{{ route('warga.ajukan.barang.tambah') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <x-pilihan nama="kategori_sampah_id" label="Jenis barang" wajib kosong="Pilih jenis rongsok">
                            @foreach ($kategori->groupBy(fn ($k) => $k->induk?->nama ?? 'Lainnya') as $namaGolongan => $daftar)
                                <optgroup label="{{ $namaGolongan }}">
                                    @foreach ($daftar as $k)
                                        <option value="{{ $k->id }}" @selected(old('kategori_sampah_id') == $k->id)>
                                            {{ $k->nama }} — {{ rupiah($k->harga_tampil) }}/{{ $k->satuan }}{{ $k->min_berat > 0 ? ' (min. '.berat($k->min_berat).')' : '' }}{{ $k->limbah_b3 ? ' ⚠ B3' : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </x-pilihan>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-kolom nama="estimasi_berat" label="Perkiraan berat" tipe="number" step="0.1" min="0.1"
                                     akhiran="kg" wajib placeholder="Contoh: 5"
                                     bantuan="Kira-kira saja. Yang dibayar adalah hasil timbangan di lokasi." />
                            <x-kolom nama="catatan" label="Catatan (opsional)" placeholder="Contoh: sudah diikat" />
                        </div>

                        <x-unggah nama="foto" label="Foto barang (opsional)"
                                  bantuan="Membantu pengepul menyiapkan kendaraan. JPG/PNG, maks. 2 MB." />

                        @if ($kategori->contains('limbah_b3', true))
                            <details class="rounded-xl bg-rose-50 p-4 text-sm dark:bg-rose-500/10">
                                <summary class="cursor-pointer font-semibold text-rose-800 dark:text-rose-300">
                                    ⚠️ Baca dulu bila menjual limbah B3
                                </summary>
                                @foreach ($kategori->where('limbah_b3', true) as $b3)
                                    <p class="mt-2 leading-relaxed text-rose-700 dark:text-rose-300">
                                        <strong>{{ $b3->nama }}:</strong> {{ $b3->peringatan_b3 }}
                                    </p>
                                @endforeach
                            </details>
                        @endif

                        <x-tombol type="submit" variant="halus" ikon="tambah">Tambahkan ke daftar</x-tombol>
                    </form>
                @endif
            </x-kartu>
        </div>

        {{-- Ringkasan keranjang --}}
        <div class="lg:col-span-2">
            <x-kartu judul="Barang yang akan dijual" :keterangan="$rincian->count().' jenis'" class="lg:sticky lg:top-20">
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($rincian as $r)
                        <div class="flex items-center gap-3 py-3 first:pt-0">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-lg dark:bg-slate-800">
                                {{ $r->kategori?->ikon }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $r->kategori?->nama }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ berat($r->berat) }} × {{ rupiah($r->harga) }}
                                    @if ($r->foto) · 📷 @endif
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($r->subtotal) }}</p>
                            <form method="POST" action="{{ route('warga.ajukan.barang.hapus', $r->indeks) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                        aria-label="Hapus {{ $r->kategori?->nama }}">
                                    <x-ikon nama="sampah" ukuran="size-4" />
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-400">Belum ada barang. Tambahkan dari form di samping.</p>
                    @endforelse
                </div>

                @if ($rincian->isNotEmpty())
                    <div class="mt-4 flex items-baseline justify-between border-t border-slate-200 pt-4 dark:border-slate-700">
                        <span class="text-sm text-slate-500 dark:text-slate-400">Perkiraan total</span>
                        <span class="text-xl font-extrabold tabular-nums text-merk-700 dark:text-merk-400">{{ rupiah($rincian->sum('subtotal')) }}</span>
                    </div>
                    <p class="mt-1 text-right text-xs text-slate-400">{{ berat($rincian->sum('berat')) }} total</p>
                @endif

                <x-slot:kaki>
                    <div class="flex items-center justify-between gap-3">
                        <form method="POST" action="{{ route('warga.ajukan.batal') }}">
                            @csrf
                            @method('DELETE')
                            <x-tombol type="submit" variant="hantu" ukuran="kecil">Batalkan</x-tombol>
                        </form>
                        @if ($rincian->isNotEmpty())
                            <x-tombol :href="route('warga.ajukan.jadwal')" variant="primer" ikon-kanan="panah-kanan">
                                Lanjut
                            </x-tombol>
                        @endif
                    </div>
                </x-slot:kaki>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
