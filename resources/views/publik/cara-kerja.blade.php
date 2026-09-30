<x-layouts.publik>
    @section('judul', 'Cara Kerja RongsokKu')

    <section class="latar-hero border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Cara Kerja RongsokKu
            </h1>
            <p class="mt-4 max-w-2xl leading-relaxed text-slate-600 dark:text-slate-400">
                Alurnya dibuat sesederhana mungkin, dan setiap tahap dirancang supaya
                tidak ada pihak yang dirugikan.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        {{-- ══ UNTUK WARGA ══ --}}
        <h2 class="flex items-center gap-2.5 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            <span class="grid size-10 place-items-center rounded-xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                <x-ikon nama="pengguna" ukuran="size-5" />
            </span>
            Untuk Warga
        </h2>

        <ol class="mt-8 space-y-4">
            @foreach ([
                ['Bandingkan harga', 'Buka Pusat Harga Pasar untuk melihat harga wajar tiap jenis rongsok, lalu lihat berapa harga yang dipasang masing-masing pengepul di sekitarmu.'],
                ['Pilih pengepul', 'Perhatikan bukan cuma harganya. Lihat juga rating, skor kepatuhan harga, dan jaraknya dari rumahmu.'],
                ['Ajukan penjemputan', 'Pilih jenis rongsok, taksir beratnya, tentukan jadwal. Harga pengepul dikunci saat ini juga dan tidak bisa diubah sepihak.'],
                ['Barang ditimbang', 'Pengepul datang sesuai jadwal dan menimbang di depanmu. Berat aktual biasanya berbeda dari taksiran, dan itu wajar.'],
                ['Konfirmasi dan dibayar', 'Kalau hasil timbangan sesuai, konfirmasi dan terima uang tunai. Kalau tidak sesuai, kamu bisa menolak dan mengajukan sengketa.'],
                ['Beri penilaian', 'Rating yang kamu berikan membantu warga lain memilih pengepul yang tepat.'],
            ] as $i => [$judul, $isi])
                <li class="flex gap-5 rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-merk-600 text-sm font-bold text-white">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $judul }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $isi }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- ══ UNTUK PENGEPUL ══ --}}
        <h2 class="mt-16 flex items-center gap-2.5 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
            <span class="grid size-10 place-items-center rounded-xl bg-nilai-50 text-nilai-600 dark:bg-nilai-500/10 dark:text-nilai-400">
                <x-ikon nama="toko" ukuran="size-5" />
            </span>
            Untuk Pengepul
        </h2>

        <ol class="mt-8 space-y-4">
            @foreach ([
                ['Daftar dan verifikasi', 'Unggah KTP dan foto lapak. Admin memeriksa berkas sebelum lapakmu tampil di pencarian.'],
                ['Pasang harga sendiri', 'Kamu yang menentukan harga beli tiap kategori. Sistem hanya menampilkan perbandingannya terhadap indeks pasar, bukan memblokir.'],
                ['Isi saldo', 'Saldo dipakai membayar komisi platform. Tanpa saldo yang cukup, kamu tidak bisa menerima permintaan baru.'],
                ['Terima permintaan', 'Permintaan masuk dari warga yang memilihmu. Ada juga permintaan terbuka yang bisa kamu klaim lebih dulu.'],
                ['Jemput dan timbang', 'Datang sesuai jadwal, timbang di lokasi, lalu masukkan berat aktual ke sistem.'],
                ['Bayar tunai', 'Bayar langsung ke warga. Komisi otomatis dipotong dari saldomu setelah warga mengonfirmasi.'],
            ] as $i => [$judul, $isi])
                <li class="flex gap-5 rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-nilai-500 text-sm font-bold text-white">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $judul }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $isi }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- ══ CATATAN PENTING ══ --}}
        <div class="mt-16 rounded-2xl bg-merk-50 p-6 ring-1 ring-merk-600/15 dark:bg-merk-500/10 dark:ring-merk-400/20">
            <h2 class="flex items-center gap-2 font-bold text-merk-900 dark:text-merk-200">
                <x-ikon nama="perisai" ukuran="size-5" />
                Uang tidak pernah melewati RongsokKu
            </h2>
            <p class="mt-3 text-sm leading-relaxed text-merk-800 dark:text-merk-300">
                Pengepul membayar warga secara tunai langsung di lokasi. RongsokKu hanya mencatat
                transaksinya dan memotong komisi dari saldo pengepul. Kami tidak pernah menahan,
                menyimpan, atau menyalurkan uang hasil penjualan rongsokmu.
            </p>
        </div>
    </section>
</x-layouts.publik>
