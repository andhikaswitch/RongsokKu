@props(['judul' => null, 'keterangan' => null])

@php
    use App\Enums\PeranPengguna;

    $pengguna = auth()->user();

    $menu = match ($pengguna->peran) {
        PeranPengguna::Warga => [
            ['rute' => 'warga.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'pengepul.cari', 'label' => 'Cari Pengepul', 'ikon' => 'cari'],
            ['rute' => 'warga.permintaan.index', 'label' => 'Permintaan Saya', 'ikon' => 'truk'],
            ['rute' => 'harga.index', 'label' => 'Harga Pasar', 'ikon' => 'grafik'],
            ['rute' => 'warga.dampak', 'label' => 'Dampak & Lencana', 'ikon' => 'daun'],
            ['rute' => 'warga.profil', 'label' => 'Profil Saya', 'ikon' => 'pengguna'],
        ],
        PeranPengguna::Pengepul => [
            ['rute' => 'pengepul.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'pengepul.permintaan.index', 'label' => 'Permintaan Masuk', 'ikon' => 'lonceng'],
            ['rute' => 'pengepul.terbuka.index', 'label' => 'Permintaan Terbuka', 'ikon' => 'petir'],
            ['rute' => 'pengepul.harga.index', 'label' => 'Daftar Harga', 'ikon' => 'grafik'],
            ['rute' => 'pengepul.dompet.index', 'label' => 'Dompet & Saldo', 'ikon' => 'dompet'],
            ['rute' => 'pengepul.performa', 'label' => 'Performa Saya', 'ikon' => 'piala'],
            ['rute' => 'pengepul.profil', 'label' => 'Profil Lapak', 'ikon' => 'toko'],
        ],
        PeranPengguna::Admin => [
            ['rute' => 'admin.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'admin.verifikasi.index', 'label' => 'Verifikasi Pengepul', 'ikon' => 'perisai'],
            ['rute' => 'admin.topup.index', 'label' => 'Verifikasi Top-up', 'ikon' => 'dompet'],
            ['rute' => 'admin.transaksi.index', 'label' => 'Transaksi', 'ikon' => 'truk'],
            ['rute' => 'admin.sengketa.index', 'label' => 'Sengketa', 'ikon' => 'peringatan'],
            ['rute' => 'admin.kategori.index', 'label' => 'Kategori Sampah', 'ikon' => 'kotak'],
            ['rute' => 'admin.harga.index', 'label' => 'Harga Acuan & Indeks', 'ikon' => 'grafik'],
            ['rute' => 'admin.pengguna.index', 'label' => 'Pengguna', 'ikon' => 'pengguna-grup'],
            ['rute' => 'admin.pengaturan', 'label' => 'Pengaturan', 'ikon' => 'pengaturan'],
        ],
    };
@endphp

<x-layouts.dasar>
    <div class="flex min-h-screen">
        {{-- Sidebar tetap pada layar lebar. --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200
                      bg-white lg:flex dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-16 items-center border-b border-slate-200 px-5 dark:border-slate-800">
                <a href="{{ route('beranda') }}"><x-logo /></a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Menu panel">
                @foreach ($menu as $m)
                    @php $aktif = request()->routeIs($m['rute']); @endphp
                    <a href="{{ route($m['rute']) }}"
                       @class([
                           'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                           'bg-merk-600 text-white shadow-sm' => $aktif,
                           'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $aktif,
                       ])>
                        <x-ikon :nama="$m['ikon']" ukuran="size-4.5" class="shrink-0" />
                        {{ $m['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-200 p-3 dark:border-slate-800">
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <x-avatar :nama="$pengguna->name" :foto="$pengguna->foto_profil" ukuran="size-9" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $pengguna->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $pengguna->peran->label() }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
                                   text-slate-600 transition hover:bg-rose-50 hover:text-rose-700
                                   dark:text-slate-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                        <x-ikon nama="keluar" ukuran="size-4.5" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            {{-- Bar atas --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200
                           bg-white/90 px-4 backdrop-blur-lg sm:px-6 dark:border-slate-800 dark:bg-slate-900/90">
                <a href="{{ route('beranda') }}" class="lg:hidden"><x-logo :teks="false" /></a>

                <div class="min-w-0 flex-1">
                    @if ($judul)
                        <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $judul }}</h1>
                        @if ($keterangan)
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $keterangan }}</p>
                        @endif
                    @endif
                </div>

                @isset($aksi)
                    <div class="shrink-0">{{ $aksi }}</div>
                @endisset

                <x-tombol-tema />
            </header>

            {{-- Navigasi bawah untuk layar kecil. --}}
            <nav class="fixed inset-x-0 bottom-0 z-40 flex border-t border-slate-200 bg-white
                        px-1 pb-[env(safe-area-inset-bottom)] lg:hidden dark:border-slate-800 dark:bg-slate-900"
                 aria-label="Menu panel ringkas">
                @foreach (array_slice($menu, 0, 5) as $m)
                    @php $aktif = request()->routeIs($m['rute']); @endphp
                    <a href="{{ route($m['rute']) }}"
                       @class([
                           'flex flex-1 flex-col items-center gap-1 rounded-lg px-1 py-2.5 text-[10px] font-medium leading-tight',
                           'text-merk-700 dark:text-merk-400' => $aktif,
                           'text-slate-500 dark:text-slate-500' => ! $aktif,
                       ])>
                        <x-ikon :nama="$m['ikon']" ukuran="size-5" />
                        <span class="line-clamp-1 text-center">{{ \Illuminate\Support\Str::before($m['label'], ' ') }}</span>
                    </a>
                @endforeach
            </nav>

            <main id="konten" class="flex-1 p-4 pb-24 sm:p-6 lg:pb-6">
                @if (session()->hasAny(['sukses', 'peringatan', 'galat', 'info']) || $errors->any())
                    <div class="mb-6"><x-notifikasi /></div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.dasar>
