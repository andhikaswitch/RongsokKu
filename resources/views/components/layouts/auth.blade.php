@props(['judul', 'keterangan' => null])

<x-layouts.dasar>
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel kiri: nilai jual, hanya tampil di layar lebar. --}}
        <div class="latar-hero relative hidden flex-col justify-between p-12 lg:flex dark:bg-slate-950">
            <div class="pola-titik pointer-events-none absolute inset-0 text-slate-900/[0.06] dark:text-white/[0.05]"></div>

            <a href="{{ route('beranda') }}" class="relative"><x-logo ukuran="size-10" /></a>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900 dark:text-white">
                    Rongsok di rumah punya harga.<br>
                    <span class="text-merk-700 dark:text-merk-400">Sekarang kamu tahu berapa.</span>
                </h2>
                <p class="mt-4 leading-relaxed text-slate-600 dark:text-slate-400">
                    Bandingkan harga antar pengepul, pilih yang paling cocok, lalu barangmu
                    dijemput langsung ke rumah.
                </p>

                <dl class="mt-10 grid grid-cols-3 gap-6">
                    <div>
                        <dt class="text-2xl font-extrabold text-merk-700 dark:text-merk-400">100%</dt>
                        <dd class="mt-1 text-xs leading-snug text-slate-600 dark:text-slate-400">responden ingin harga transparan</dd>
                    </div>
                    <div>
                        <dt class="text-2xl font-extrabold text-merk-700 dark:text-merk-400">90%</dt>
                        <dd class="mt-1 text-xs leading-snug text-slate-600 dark:text-slate-400">tertarik memakai platform</dd>
                    </div>
                    <div>
                        <dt class="text-2xl font-extrabold text-merk-700 dark:text-merk-400">6</dt>
                        <dd class="mt-1 text-xs leading-snug text-slate-600 dark:text-slate-400">golongan rongsok diterima</dd>
                    </div>
                </dl>
            </div>

            <p class="relative text-xs text-slate-500 dark:text-slate-500">
                Berdasarkan validasi pasar terhadap 10 warga dan 2 pengepul.
            </p>
        </div>

        {{-- Panel kanan: formulir. --}}
        <div class="flex items-center justify-center bg-white px-4 py-12 sm:px-8 dark:bg-slate-900">
            <div class="w-full max-w-md">
                <a href="{{ route('beranda') }}" class="mb-8 inline-flex lg:hidden">
                    <x-logo />
                </a>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $judul }}</h1>
                @if ($keterangan)
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $keterangan }}</p>
                @endif

                <div class="mt-6">
                    <x-notifikasi />
                </div>

                <div class="mt-6">
                    {{ $slot }}
                </div>

                @isset($kaki)
                    <div class="mt-8 border-t border-slate-200 pt-6 text-center text-sm dark:border-slate-800">
                        {{ $kaki }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
</x-layouts.dasar>
