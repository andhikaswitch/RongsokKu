<x-layouts.auth judul="Masuk ke RongsokKu"
                keterangan="Belum punya akun? Daftar gratis, tidak dipungut biaya apa pun.">
    @section('judul', 'Masuk')

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-kolom label="Alamat Email" nama="email" tipe="email" wajib
                 autocomplete="username" placeholder="nama@email.com" autofocus />

        <x-kolom label="Kata Sandi" nama="password" tipe="password" wajib
                 autocomplete="current-password" placeholder="••••••••" />

        <label class="flex cursor-pointer items-center gap-2.5">
            <input type="checkbox" name="ingat" value="1"
                   class="size-4 rounded border-slate-300 text-merk-600 focus:ring-merk-500 dark:border-slate-600 dark:bg-slate-800">
            <span class="text-sm text-slate-600 dark:text-slate-400">Biarkan saya tetap masuk</span>
        </label>

        <x-tombol type="submit" variant="primer" ukuran="besar" penuh ikon="masuk">
            Masuk
        </x-tombol>
    </form>

    {{-- Akun demo untuk keperluan pengujian dan sidang. --}}
    <div class="mt-8 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/60 dark:bg-slate-800/50 dark:ring-slate-700/60">
        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <x-ikon nama="info" ukuran="size-3.5" />
            Akun Demo
        </p>

        <div class="mt-3 space-y-1.5 text-xs">
            @foreach ([
                ['Warga', 'andhika@warga.test'],
                ['Pengepul', 'jaya@pengepul.test'],
                ['Admin', 'admin@rongsokku.test'],
            ] as [$peran, $email])
                <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 dark:bg-slate-900">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $peran }}</span>
                    <code class="truncate font-mono text-slate-500 dark:text-slate-400">{{ $email }}</code>
                </div>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
            Kata sandi semua akun: <code class="font-mono font-bold text-slate-700 dark:text-slate-300">password</code>
        </p>
    </div>

    <x-slot:kaki>
        <span class="text-slate-500 dark:text-slate-400">Belum punya akun?</span>
        <a href="{{ route('register') }}" class="font-semibold text-merk-700 hover:text-merk-800 dark:text-merk-400 dark:hover:text-merk-300">
            Daftar sekarang
        </a>
    </x-slot:kaki>
</x-layouts.auth>
