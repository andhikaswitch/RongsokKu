<x-layouts.panel judul="Ajukan Penjemputan" keterangan="Pilih cara mengajukan">
    @section('judul', 'Ajukan Penjemputan')

    <x-langkah :aktif="1" class="mb-8 max-w-2xl" />

    @if ($lanjutkan)
        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-sky-50 p-5 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:ring-sky-400/30">
            <x-ikon nama="info" ukuran="size-5" class="shrink-0 text-sky-600 dark:text-sky-400" />
            <p class="min-w-0 flex-1 text-sm text-sky-800 dark:text-sky-200">
                Anda punya pengajuan yang belum selesai.
            </p>
            <x-tombol :href="route('warga.ajukan.barang')" variant="primer" ukuran="kecil" ikon-kanan="panah-kanan">
                Lanjutkan
            </x-tombol>
        </div>
    @endif

    <div class="grid max-w-4xl gap-5 md:grid-cols-2">
        <a href="{{ route('pengepul.cari') }}"
           class="group flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200/80 transition hover:shadow-[var(--shadow-naik)] hover:ring-merk-300 dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-merk-700">
            <span class="grid size-12 place-items-center rounded-2xl bg-merk-50 text-merk-600 transition group-hover:scale-105 dark:bg-merk-500/10 dark:text-merk-400">
                <x-ikon nama="cari" ukuran="size-6" />
            </span>
            <h2 class="mt-5 text-lg font-bold text-slate-900 dark:text-white">Pilih pengepul sendiri</h2>
            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Bandingkan harga, rating, dan skor kepatuhan harga, lalu tekan
                <strong class="font-semibold">Ajukan Jemput</strong> di profil pengepul pilihanmu.
                Harga pengepul langsung dikunci.
            </p>
            <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-merk-700 dark:text-merk-400">
                Cari pengepul <x-ikon nama="panah-kanan" ukuran="size-4" />
            </span>
            <x-lencana warna="merk" class="mt-4 self-start">Disarankan</x-lencana>
        </a>

        <a href="{{ route('warga.ajukan', ['terbuka' => 1]) }}"
           class="group flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200/80 transition hover:shadow-[var(--shadow-naik)] hover:ring-violet-300 dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-violet-700">
            <span class="grid size-12 place-items-center rounded-2xl bg-violet-50 text-violet-600 transition group-hover:scale-105 dark:bg-violet-500/10 dark:text-violet-400">
                <x-ikon nama="petir" ukuran="size-6" />
            </span>
            <h2 class="mt-5 text-lg font-bold text-slate-900 dark:text-white">Permintaan terbuka</h2>
            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Belum tahu pengepul mana? Tawarkan ke semua pengepul terverifikasi di sekitarmu.
                Siapa yang mengklaim lebih dulu, dia yang menjemput, dengan harga yang ia pasang.
            </p>
            <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-violet-700 dark:text-violet-400">
                Buat permintaan terbuka <x-ikon nama="panah-kanan" ukuran="size-4" />
            </span>
        </a>
    </div>
</x-layouts.panel>
