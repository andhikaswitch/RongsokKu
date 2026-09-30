<x-layouts.dasar>
    @php
        $tautan = [
            ['rute' => 'beranda', 'label' => 'Beranda'],
            ['rute' => 'harga.index', 'label' => 'Harga Pasar'],
            ['rute' => 'pengepul.cari', 'label' => 'Cari Pengepul'],
            ['rute' => 'peringkat', 'label' => 'Peringkat'],
            ['rute' => 'cara-kerja', 'label' => 'Cara Kerja'],
        ];
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/85 backdrop-blur-lg
                   dark:border-slate-800/70 dark:bg-slate-950/85">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('beranda') }}" class="shrink-0">
                <x-logo />
            </a>

            <nav class="ml-4 hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                @foreach ($tautan as $t)
                    <a href="{{ route($t['rute']) }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-medium transition',
                           'bg-merk-50 text-merk-700 dark:bg-merk-500/10 dark:text-merk-300' => request()->routeIs($t['rute']),
                           'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! request()->routeIs($t['rute']),
                       ])>
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <x-tombol-tema />

                @auth
                    <x-tombol :href="route(auth()->user()->peran->beranda())" variant="primer" ukuran="kecil" ikon="rumah">
                        Dashboard
                    </x-tombol>
                @else
                    <x-tombol :href="route('login')" variant="hantu" ukuran="kecil" class="hidden sm:inline-flex">
                        Masuk
                    </x-tombol>
                    <x-tombol :href="route('register')" variant="primer" ukuran="kecil">
                        Daftar Gratis
                    </x-tombol>
                @endauth

                {{-- Menu mobile memakai checkbox + peer, murni CSS. --}}
                <label class="lg:hidden">
                    <input type="checkbox" class="peer sr-only">
                    <span class="grid size-9 cursor-pointer place-items-center rounded-xl text-slate-600
                                 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                        <x-ikon nama="menu" ukuran="size-5" />
                    </span>
                    <span class="invisible fixed inset-x-0 top-16 z-30 origin-top scale-y-95 border-b border-slate-200
                                 bg-white p-3 opacity-0 shadow-lg transition-all duration-200
                                 peer-checked:visible peer-checked:scale-y-100 peer-checked:opacity-100
                                 dark:border-slate-800 dark:bg-slate-900">
                        <span class="mx-auto block max-w-7xl space-y-1">
                            @foreach ($tautan as $t)
                                <a href="{{ route($t['rute']) }}"
                                   class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700
                                          hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                    {{ $t['label'] }}
                                </a>
                            @endforeach
                            @guest
                                <a href="{{ route('login') }}"
                                   class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700
                                          hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Masuk
                                </a>
                            @endguest
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </header>

    <main id="konten">
        @if (session()->hasAny(['sukses', 'peringatan', 'galat', 'info']))
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <x-notifikasi />
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-24 border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-4">
                <div class="md:col-span-2">
                    <x-logo />
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Marketplace kiloan barang bekas yang menghubungkan warga dengan pengepul
                        terdekat. Harga transparan, barang dijemput sampai ke rumah.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <x-lencana warna="merk" ikon="daun">Ramah Lingkungan</x-lencana>
                        <x-lencana warna="sky" ikon="perisai">Pengepul Terverifikasi</x-lencana>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Jelajahi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($tautan as $t)
                            <li>
                                <a href="{{ route($t['rute']) }}"
                                   class="text-slate-600 transition hover:text-merk-700 dark:text-slate-400 dark:hover:text-merk-400">
                                    {{ $t['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Informasi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('tentang') }}" class="text-slate-600 transition hover:text-merk-700 dark:text-slate-400 dark:hover:text-merk-400">Tentang Kami</a></li>
                        <li><a href="{{ route('faq') }}" class="text-slate-600 transition hover:text-merk-700 dark:text-slate-400 dark:hover:text-merk-400">Tanya Jawab</a></li>
                        <li><a href="{{ route('register') }}" class="text-slate-600 transition hover:text-merk-700 dark:text-slate-400 dark:hover:text-merk-400">Daftar Pengepul</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-3 border-t border-slate-200 pt-6 text-xs text-slate-500
                        sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:text-slate-500">
                <p>&copy; {{ date('Y') }} RongsokKu &middot; Proyek Mata Kuliah Framework Pemrograman Web</p>
                <p>Informatika 5B &middot; Universitas Singaperbangsa Karawang</p>
            </div>
        </div>
    </footer>
</x-layouts.dasar>
