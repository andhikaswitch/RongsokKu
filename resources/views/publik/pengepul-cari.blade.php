<x-layouts.publik>
    @section('judul', 'Cari Pengepul Terdekat')

    <section class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/40">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Cari Pengepul Terdekat
            </h1>
            <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">
                Bandingkan harga, rekam jejak, dan jarak sebelum memilih. Semua pengepul di sini
                sudah diverifikasi identitas dan lokasi lapaknya.
            </p>

            {{-- Penyaring: form GET murni, diproses Controller. --}}
            <form method="GET" action="{{ route('pengepul.cari') }}" class="mt-8">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <x-kolom nama="q" label="Nama pengepul" :nilai="$filter['q'] ?? ''"
                             placeholder="Cari nama lapak..." />

                    <x-pilihan nama="wilayah" label="Kecamatan" :opsi="$kecamatan"
                               :nilai="$filter['wilayah'] ?? ''" kosong="Semua kecamatan" />

                    <x-pilihan nama="kategori" label="Menerima kategori" :opsi="$kategori"
                               :nilai="$filter['kategori'] ?? ''" kosong="Semua kategori" />

                    <x-pilihan nama="urut" label="Urutkan"
                               :opsi="[
                                   'jarak' => 'Terdekat',
                                   'rating' => 'Rating tertinggi',
                                   'kepatuhan' => 'Kepatuhan harga terbaik',
                                   'transaksi' => 'Paling banyak transaksi',
                               ]"
                               :nilai="$filter['urut'] ?? 'jarak'" :kosong="false" />
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="buka" value="1" @checked(! empty($filter['buka']))
                               class="size-4 rounded border-slate-300 text-merk-600 focus:ring-merk-500 dark:border-slate-600 dark:bg-slate-800">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Sedang menerima</span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="b3" value="1" @checked(! empty($filter['b3']))
                               class="size-4 rounded border-slate-300 text-merk-600 focus:ring-merk-500 dark:border-slate-600 dark:bg-slate-800">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Berizin limbah B3</span>
                    </label>

                    <div class="ml-auto flex gap-2">
                        @if (array_filter($filter))
                            <x-tombol :href="route('pengepul.cari')" variant="hantu" ukuran="kecil">
                                Atur ulang
                            </x-tombol>
                        @endif
                        <x-tombol type="submit" variant="primer" ikon="saring">Terapkan</x-tombol>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Ditemukan <strong class="font-semibold text-slate-900 dark:text-white">{{ $pengepul->total() }}</strong> pengepul
            @auth
                @if (auth()->user()->latitude && ($filter['urut'] ?? 'jarak') === 'jarak')
                    &middot; diurutkan dari lokasi Anda
                @endif
            @endauth
        </p>

        @if ($pengepul->isEmpty())
            <x-kartu class="mt-6">
                <x-kosong ikon="cari" judul="Tidak ada pengepul yang cocok"
                          pesan="Coba longgarkan penyaring, atau pilih kecamatan lain.">
                    <x-tombol :href="route('pengepul.cari')" variant="sekunder">Atur ulang penyaring</x-tombol>
                </x-kosong>
            </x-kartu>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pengepul as $p)
                    @include('publik.partials.kartu-pengepul', ['p' => $p])
                @endforeach
            </div>

            <div class="mt-10">
                {{ $pengepul->links() }}
            </div>
        @endif
    </section>
</x-layouts.publik>
