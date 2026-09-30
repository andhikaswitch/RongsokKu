<x-layouts.publik>
    @section('judul', 'Jual Rongsok Jadi Mudah')

    {{-- ══ HERO ══ --}}
    <section class="latar-hero relative overflow-hidden">
        <div class="pola-titik pointer-events-none absolute inset-0 text-slate-900/[0.05] dark:text-white/[0.04]"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid items-center gap-14 lg:grid-cols-2">
                <div>
                    <x-lencana warna="merk" ikon="petir" class="mb-5">
                        Harga diperbarui dari transaksi nyata
                    </x-lencana>

                    <h1 class="text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                        Rongsok di rumah<br>
                        <span class="bg-gradient-to-r from-merk-600 to-merk-400 bg-clip-text text-transparent">
                            punya harga.
                        </span>
                    </h1>

                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                        Bandingkan harga antar pengepul di sekitarmu, pilih yang paling cocok,
                        lalu barangmu dijemput langsung ke rumah. Dibayar tunai di tempat.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
                        <x-tombol :href="route('pengepul.cari')" variant="primer" ukuran="besar" ikon="cari">
                            Cari Pengepul Terdekat
                        </x-tombol>
                        <x-tombol :href="route('harga.index')" variant="sekunder" ukuran="besar" ikon="grafik">
                            Lihat Harga Pasar
                        </x-tombol>
                    </div>

                    <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-slate-200/80 pt-8 dark:border-slate-700/60">
                        <div>
                            <dt class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ number_format($statistik['berat'], 0, ',', '.') }}
                                <span class="text-sm font-bold text-slate-400">kg</span>
                            </dt>
                            <dd class="mt-1 text-xs leading-snug text-slate-500 dark:text-slate-400">diselamatkan dari TPA</dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ number_format($statistik['transaksi'], 0, ',', '.') }}
                            </dt>
                            <dd class="mt-1 text-xs leading-snug text-slate-500 dark:text-slate-400">transaksi selesai</dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                                {{ $statistik['pengepul'] }}
                            </dt>
                            <dd class="mt-1 text-xs leading-snug text-slate-500 dark:text-slate-400">pengepul terverifikasi</dd>
                        </div>
                    </dl>
                </div>

                {{-- Kartu harga melayang --}}
                <div class="relative lg:pl-8">
                    <div class="rounded-3xl bg-white/80 p-2 shadow-[var(--shadow-naik)] ring-1 ring-slate-200/60 backdrop-blur
                                dark:bg-slate-900/80 dark:ring-slate-700/60">
                        <div class="rounded-[1.25rem] bg-white p-6 dark:bg-slate-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-merk-700 dark:text-merk-400">
                                        Indeks RongsokKu
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-400">Dari transaksi 30 hari terakhir</p>
                                </div>
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-merk-700 dark:text-merk-400">
                                    <span class="denyut-lembut size-2 rounded-full bg-merk-500"></span>
                                    Aktif
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                @forelse ($indeksSorot as $idx)
                                    <a href="{{ route('harga.show', $idx->kategori) }}"
                                       class="flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-lg dark:bg-slate-800">
                                            {{ $idx->kategori->ikon }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $idx->kategori->nama }}
                                            </p>
                                            <p class="text-xs text-slate-400">{{ $idx->jumlah_transaksi }} transaksi</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-slate-900 tabular-nums dark:text-white">
                                                {{ rupiah($idx->harga_median) }}
                                            </p>
                                            @if ($idx->perubahan_persen !== null && abs((float) $idx->perubahan_persen) >= 0.05)
                                                <p @class([
                                                    'text-xs font-semibold tabular-nums',
                                                    'text-emerald-600 dark:text-emerald-400' => $idx->perubahan_persen > 0,
                                                    'text-rose-600 dark:text-rose-400' => $idx->perubahan_persen < 0,
                                                ])>
                                                    {{ \App\Support\Format::persen((float) $idx->perubahan_persen) }}
                                                </p>
                                            @else
                                                <p class="text-xs text-slate-400">stabil</p>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <p class="py-6 text-center text-sm text-slate-400">
                                        Indeks sedang dihitung. Data akan tampil setelah transaksi cukup.
                                    </p>
                                @endforelse
                            </div>

                            <a href="{{ route('harga.index') }}"
                               class="mt-4 flex items-center justify-center gap-1.5 rounded-xl bg-slate-50 py-2.5
                                      text-sm font-semibold text-slate-700 transition hover:bg-slate-100
                                      dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                Lihat semua harga
                                <x-ikon nama="panah-kanan" ukuran="size-3.5" />
                            </a>
                        </div>
                    </div>

                    <div class="pointer-events-none absolute -bottom-5 -left-3 hidden rounded-2xl bg-white p-4
                                shadow-[var(--shadow-naik)] ring-1 ring-slate-200/60 lg:block
                                dark:bg-slate-900 dark:ring-slate-700/60">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                                <x-ikon nama="daun" ukuran="size-5" />
                            </span>
                            <div>
                                <p class="text-lg font-extrabold leading-none text-slate-900 tabular-nums dark:text-white">
                                    {{ number_format($statistik['co2'], 0, ',', '.') }} kg
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">CO₂ dicegah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ CARA KERJA ══ --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Empat langkah, selesai
            </h2>
            <p class="mt-4 text-slate-600 dark:text-slate-400">
                Tidak perlu tawar-menawar yang bikin canggung. Harga sudah terlihat sejak awal.
            </p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['cari', 'Pilih pengepul', 'Lihat daftar pengepul terdekat lengkap dengan harga per kilogram dan rekam jejaknya.'],
                ['kotak', 'Ajukan barangmu', 'Pilih jenis rongsok, taksir beratnya, tentukan jadwal jemput. Harga langsung dikunci.'],
                ['truk', 'Pengepul datang', 'Barang ditimbang di depan matamu. Berat dan harga akhir dikonfirmasi bersama.'],
                ['dompet', 'Dibayar tunai', 'Uang diterima langsung di tempat. Terakhir, beri penilaian untuk pengepul.'],
            ] as $i => [$ikon, $judul, $isi])
                <div class="group relative rounded-2xl bg-white p-6 ring-1 ring-slate-200/80 transition
                            hover:shadow-[var(--shadow-naik)] dark:bg-slate-900 dark:ring-slate-800">
                    <span class="absolute right-5 top-5 text-4xl font-extrabold text-slate-100 dark:text-slate-800">
                        {{ $i + 1 }}
                    </span>
                    <span class="relative grid size-12 place-items-center rounded-2xl bg-merk-50 text-merk-600
                                 transition group-hover:scale-105 dark:bg-merk-500/10 dark:text-merk-400">
                        <x-ikon :nama="$ikon" ukuran="size-6" />
                    </span>
                    <h3 class="relative mt-5 font-bold text-slate-900 dark:text-white">{{ $judul }}</h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══ GOLONGAN RONGSOK ══ --}}
    <section class="border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/40">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Yang kami terima
                    </h2>
                    <p class="mt-3 max-w-xl text-slate-600 dark:text-slate-400">
                        Enam golongan rongsok, masing-masing punya harga acuan yang bisa kamu cek sendiri.
                    </p>
                </div>
                <x-tombol :href="route('harga.index')" variant="sekunder" ikon-kanan="panah-kanan">
                    Semua harga
                </x-tombol>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($golongan as $g)
                    <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200/60 transition
                                hover:ring-merk-300 dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-merk-700">
                        <div class="flex items-start gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white text-2xl
                                         shadow-sm ring-1 ring-slate-200/60 dark:bg-slate-800 dark:ring-slate-700">
                                {{ $g->ikon }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ $g->nama }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                    {{ $g->deskripsi }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-1.5">
                            @foreach ($g->anak as $anak)
                                <a href="{{ route('harga.show', $anak) }}"
                                   @class([
                                       'rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset transition',
                                       'bg-rose-50 text-rose-700 ring-rose-200 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30' => $anak->limbah_b3,
                                       'bg-white text-slate-600 ring-slate-200 hover:bg-merk-50 hover:text-merk-700 hover:ring-merk-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' => ! $anak->limbah_b3,
                                   ])>
                                    @if ($anak->limbah_b3) ⚠️ @endif{{ $anak->nama }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ PENGEPUL UNGGULAN ══ --}}
    @if ($pengepulUnggulan->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Pengepul dengan penilaian terbaik
                    </h2>
                    <p class="mt-3 text-slate-600 dark:text-slate-400">
                        Sudah diverifikasi identitas dan lokasi lapaknya.
                    </p>
                </div>
                <x-tombol :href="route('pengepul.cari')" variant="sekunder" ikon-kanan="panah-kanan">
                    Lihat semua
                </x-tombol>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ($pengepulUnggulan as $p)
                    @include('publik.partials.kartu-pengepul', ['p' => $p])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ══ AJAKAN ══ --}}
    <section class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-merk-700 via-merk-600 to-merk-800 px-8 py-16 sm:px-14">
            <div class="pola-titik pointer-events-none absolute inset-0 text-white/10"></div>

            <div class="relative grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        Punya lapak rongsok?
                    </h2>
                    <p class="mt-4 max-w-md leading-relaxed text-merk-50">
                        Dapatkan pasokan tetap dari warga sekitar, dan catat semua transaksi
                        secara rapi tanpa bon kertas lagi.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-tombol :href="route('register', ['peran' => 'pengepul'])"
                                  variant="sekunder" ukuran="besar" ikon="toko">
                            Daftar Jadi Pengepul
                        </x-tombol>
                        <x-tombol :href="route('cara-kerja')" ukuran="besar"
                                  class="bg-white/10 text-white ring-1 ring-white/25 hover:bg-white/20">
                            Pelajari Dulu
                        </x-tombol>
                    </div>
                </div>

                <ul class="relative space-y-4">
                    @foreach ([
                        'Tentukan harga belimu sendiri per kategori',
                        'Terima permintaan jemput dari warga terdekat',
                        'Rekap transaksi otomatis, tidak perlu bon kertas',
                        'Bangun reputasi lewat rating dan metrik kepatuhan harga',
                    ] as $manfaat)
                        <li class="flex items-start gap-3 text-merk-50">
                            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-white/20">
                                <x-ikon nama="cek" ukuran="size-3" class="text-white" />
                            </span>
                            <span class="text-sm leading-relaxed">{{ $manfaat }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</x-layouts.publik>
