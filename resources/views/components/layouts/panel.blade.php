@props(['judul' => null, 'keterangan' => null])

@php
    use App\Enums\PeranPengguna;

    $pengguna = auth()->user();
    $profil = $pengguna->profilPengepul;

    // Angka kecil di samping menu: hal yang sedang menunggu tindakan.
    $hitung = match ($pengguna->peran) {
        PeranPengguna::Warga => [
            'permintaan' => $pengguna->permintaan()->where('status', \App\Enums\StatusPermintaan::MenungguKonfirmasi)->count(),
        ],
        PeranPengguna::Pengepul => [
            'permintaan' => $profil?->permintaan()->where('status', \App\Enums\StatusPermintaan::Diajukan)->count() ?? 0,
        ],
        PeranPengguna::Admin => [
            'verifikasi' => \App\Models\ProfilPengepul::where('status_verifikasi', \App\Enums\StatusVerifikasi::Menunggu)->count(),
            'topup' => \App\Models\PermintaanTopup::where('status', \App\Enums\StatusTopup::Menunggu)->count(),
            'sengketa' => \App\Models\Sengketa::where('status', 'menunggu')->count(),
        ],
    };

    // 'pola' menandai menu tetap aktif saat berada di sub-halaman.
    $menu = match ($pengguna->peran) {
        PeranPengguna::Warga => [
            ['rute' => 'warga.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'warga.ajukan', 'pola' => 'warga.ajukan*', 'label' => 'Ajukan Jemput', 'ikon' => 'tambah'],
            ['rute' => 'warga.permintaan.index', 'pola' => 'warga.permintaan.*', 'label' => 'Permintaan Saya', 'ikon' => 'truk', 'jumlah' => $hitung['permintaan']],
            ['rute' => 'pengepul.cari', 'label' => 'Cari Pengepul', 'ikon' => 'cari'],
            ['rute' => 'harga.index', 'label' => 'Harga Pasar', 'ikon' => 'grafik'],
            ['rute' => 'warga.dampak', 'label' => 'Dampak & Lencana', 'ikon' => 'daun'],
            ['rute' => 'warga.profil', 'label' => 'Profil Saya', 'ikon' => 'pengguna'],
        ],
        PeranPengguna::Pengepul => [
            ['rute' => 'pengepul.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'pengepul.permintaan.index', 'pola' => 'pengepul.permintaan.*', 'label' => 'Permintaan Masuk', 'ikon' => 'lonceng', 'jumlah' => $hitung['permintaan']],
            ['rute' => 'pengepul.terbuka.index', 'label' => 'Permintaan Terbuka', 'ikon' => 'petir'],
            ['rute' => 'pengepul.harga.index', 'label' => 'Daftar Harga', 'ikon' => 'grafik'],
            ['rute' => 'pengepul.dompet.index', 'pola' => 'pengepul.dompet.*', 'label' => 'Dompet & Saldo', 'ikon' => 'dompet'],
            ['rute' => 'pengepul.performa', 'label' => 'Performa & Ulasan', 'ikon' => 'piala'],
            ['rute' => 'pengepul.profil', 'label' => 'Profil Lapak', 'ikon' => 'toko'],
            ['rute' => 'pengepul.akun', 'label' => 'Akun Saya', 'ikon' => 'pengguna'],
        ],
        PeranPengguna::Admin => [
            ['rute' => 'admin.dashboard', 'label' => 'Dashboard', 'ikon' => 'rumah'],
            ['rute' => 'admin.verifikasi.index', 'pola' => 'admin.verifikasi.*', 'label' => 'Verifikasi Pengepul', 'ikon' => 'perisai', 'jumlah' => $hitung['verifikasi']],
            ['rute' => 'admin.topup.index', 'pola' => 'admin.topup.*', 'label' => 'Verifikasi Top-up', 'ikon' => 'dompet', 'jumlah' => $hitung['topup']],
            ['rute' => 'admin.transaksi.index', 'pola' => 'admin.transaksi.*', 'label' => 'Transaksi', 'ikon' => 'truk'],
            ['rute' => 'admin.sengketa.index', 'pola' => 'admin.sengketa.*', 'label' => 'Sengketa', 'ikon' => 'peringatan', 'jumlah' => $hitung['sengketa']],
            ['rute' => 'admin.kategori.index', 'pola' => 'admin.kategori.*', 'label' => 'Kategori Sampah', 'ikon' => 'kotak'],
            ['rute' => 'admin.harga.index', 'pola' => 'admin.harga.*', 'label' => 'Harga Acuan & Indeks', 'ikon' => 'grafik'],
            ['rute' => 'admin.pengguna.index', 'pola' => 'admin.pengguna.*', 'label' => 'Pengguna', 'ikon' => 'pengguna-grup'],
            ['rute' => 'admin.pengaturan', 'label' => 'Pengaturan', 'ikon' => 'pengaturan'],
            ['rute' => 'admin.log', 'label' => 'Log Audit', 'ikon' => 'dokumen'],
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
                    @php $aktif = request()->routeIs($m['pola'] ?? $m['rute']); @endphp
                    <a href="{{ route($m['rute']) }}"
                       @if ($aktif) aria-current="page" @endif
                       @class([
                           'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                           'bg-merk-600 text-white shadow-sm' => $aktif,
                           'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $aktif,
                       ])>
                        <x-ikon :nama="$m['ikon']" ukuran="size-4.5" class="shrink-0" />
                        <span class="flex-1">{{ $m['label'] }}</span>
                        @if (! empty($m['jumlah']))
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-bold tabular-nums',
                                'bg-white/25 text-white' => $aktif,
                                'bg-rose-500 text-white' => ! $aktif,
                            ])>{{ $m['jumlah'] }}</span>
                        @endif
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
                @foreach (array_slice($menu, 0, 4) as $m)
                    @php $aktif = request()->routeIs($m['pola'] ?? $m['rute']); @endphp
                    <a href="{{ route($m['rute']) }}"
                       @class([
                           'relative flex flex-1 flex-col items-center gap-1 rounded-lg px-1 py-2.5 text-[10px] font-medium leading-tight',
                           'text-merk-700 dark:text-merk-400' => $aktif,
                           'text-slate-500 dark:text-slate-500' => ! $aktif,
                       ])>
                        <x-ikon :nama="$m['ikon']" ukuran="size-5" />
                        <span class="line-clamp-1 text-center">{{ \Illuminate\Support\Str::before($m['label'], ' ') }}</span>
                        @if (! empty($m['jumlah']))
                            <span class="absolute right-1/4 top-1.5 size-2 rounded-full bg-rose-500"></span>
                        @endif
                    </a>
                @endforeach

                {{-- Menu lengkap dibuka sebagai modal :target, tanpa JavaScript. --}}
                <a href="#menu-lengkap"
                   class="flex flex-1 flex-col items-center gap-1 rounded-lg px-1 py-2.5 text-[10px] font-medium leading-tight text-slate-500">
                    <x-ikon nama="menu" ukuran="size-5" />
                    <span>Lainnya</span>
                </a>
            </nav>

            <x-modal id="menu-lengkap" judul="Menu" :keterangan="$pengguna->name.' · '.$pengguna->peran->label()">
                <div class="space-y-1">
                    @foreach ($menu as $m)
                        <a href="{{ route($m['rute']) }}"
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            <x-ikon :nama="$m['ikon']" ukuran="size-4.5" />
                            <span class="flex-1">{{ $m['label'] }}</span>
                            @if (! empty($m['jumlah']))
                                <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white">{{ $m['jumlah'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                    @csrf
                    <x-tombol variant="bahaya-halus" penuh ikon="keluar">Keluar</x-tombol>
                </form>
            </x-modal>

            <main id="konten" class="flex-1 p-4 pb-24 sm:p-6 lg:pb-6">
                @if (session()->hasAny(['sukses', 'peringatan', 'galat', 'info']) || $errors->any())
                    <div class="mb-6"><x-notifikasi /></div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.dasar>
