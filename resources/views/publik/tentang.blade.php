<x-layouts.publik>
    @section('judul', 'Tentang RongsokKu')

    <section class="latar-hero border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Tentang RongsokKu
            </h1>
            <p class="mt-4 max-w-2xl leading-relaxed text-slate-600 dark:text-slate-400">
                Proyek mata kuliah Framework Pemrograman Web, dibangun dari hasil validasi pasar
                terhadap warga dan pengepul di Karawang.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Masalah yang kami temukan</h2>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 text-center ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                <p class="text-3xl font-extrabold text-merk-700 dark:text-merk-400">100%</p>
                <p class="mt-2 text-sm leading-snug text-slate-600 dark:text-slate-400">
                    responden warga mengeluhkan harga rongsok yang tidak transparan
                </p>
            </div>
            <div class="rounded-2xl bg-white p-5 text-center ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                <p class="text-3xl font-extrabold text-merk-700 dark:text-merk-400">90%</p>
                <p class="mt-2 text-sm leading-snug text-slate-600 dark:text-slate-400">
                    tertarik atau sangat tertarik pada solusi berbentuk platform
                </p>
            </div>
            <div class="rounded-2xl bg-white p-5 text-center ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                <p class="text-3xl font-extrabold text-merk-700 dark:text-merk-400">10 + 2</p>
                <p class="mt-2 text-sm leading-snug text-slate-600 dark:text-slate-400">
                    responden warga dan narasumber pengepul yang kami wawancarai
                </p>
            </div>
        </div>

        <div class="mt-10 space-y-5 text-slate-600 dark:text-slate-400">
            <p class="leading-relaxed">
                Warga punya rongsok menumpuk di rumah, tapi tidak tahu harga wajarnya berapa.
                Mereka menunggu pemulung lewat, lalu menerima harga apa pun yang ditawarkan karena
                tidak punya pembanding. Pengepul di sisi lain kesulitan mendapat pasokan tetap,
                dan masih mencatat transaksi dengan bon kertas.
            </p>
            <p class="leading-relaxed">
                RongsokKu menjawab keduanya dengan satu hal: <strong class="font-semibold text-slate-900 dark:text-white">membuat
                harga terlihat</strong>. Warga bisa membandingkan harga antar pengepul sebelum memilih,
                dan pengepul mendapat pembanding netral supaya tidak dituduh menekan harga.
            </p>
        </div>

        <h2 class="mt-14 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Tim Pengembang</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Informatika 5B &middot; Universitas Singaperbangsa Karawang</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Muhammad Rizky Rajabi', '2410631170039', 'Modul Warga'],
                ['Defry Ananta Perangin Angin', '2410631170066', 'Modul Pengepul'],
                ['Diego Andreas Simanjuntak', '2410631170068', 'Modul Admin & Indeks Harga'],
                ['Andhika Eka Pratama', '2410631170129', 'Arsitektur & Sistem Desain'],
            ] as [$nama, $nim, $peran])
                <div class="flex items-center gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                    <x-avatar :nama="$nama" ukuran="size-11" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $nama }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $nim }}</p>
                        <p class="mt-0.5 truncate text-xs font-medium text-merk-700 dark:text-merk-400">{{ $peran }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <h2 class="mt-14 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Teknologi</h2>
        <div class="mt-5 flex flex-wrap gap-2">
            @foreach (['Laravel 12', 'PHP 8.2', 'Blade', 'Tailwind CSS 4', 'MySQL', 'Vite', 'OpenStreetMap'] as $t)
                <x-lencana warna="slate">{{ $t }}</x-lencana>
            @endforeach
        </div>

        <p class="mt-5 rounded-2xl bg-slate-100 p-5 text-sm leading-relaxed text-slate-600 dark:bg-slate-800/50 dark:text-slate-400">
            Situs ini dibangun <strong class="font-semibold text-slate-900 dark:text-white">tanpa satu baris JavaScript pun</strong>.
            Seluruh interaksi, mulai dari menu, penyaring, mode gelap, sampai grafik tren harga,
            dikerjakan di sisi server atau dengan CSS dan SVG murni.
        </p>
    </section>
</x-layouts.publik>
