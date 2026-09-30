<x-layouts.publik>
    @section('judul', 'Tanya Jawab')

    <section class="latar-hero border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Tanya Jawab
            </h1>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        @php
            $tanya = [
                'Untuk Warga' => [
                    ['Apakah RongsokKu gratis?', 'Gratis sepenuhnya untuk warga. Kamu tidak dipungut biaya apa pun, dan uang hasil penjualan rongsok kamu terima utuh. Platform hanya memungut komisi dari pengepul.'],
                    ['Bagaimana kalau berat aktual berbeda dari taksiran saya?', 'Itu normal dan memang diharapkan. Taksiran kamu hanya dipakai untuk memperkirakan harga. Yang menentukan pembayaran adalah hasil timbangan di lokasi, yang bisa kamu saksikan sendiri.'],
                    ['Kalau pengepul menurunkan harga saat sudah di rumah saya?', 'Jangan dikonfirmasi. Tolak hasil timbangannya, dan permintaan otomatis masuk ke status sengketa untuk ditangani admin. Sistem menyimpan harga yang dipajang saat kamu mengajukan, jadi selisihnya terlihat jelas.'],
                    ['Kenapa ada pengepul dengan harga jauh lebih murah?', 'Setiap pengepul bebas menentukan harganya. Yang kami lakukan adalah menandai seberapa jauh harga itu dari indeks pasar, supaya kamu bisa menilai sendiri. Harga murah belum tentu buruk, bisa jadi lapaknya kecil atau jauh.'],
                    ['Apa itu skor kepatuhan harga?', 'Persentase transaksi yang dibayar sesuai atau di atas harga yang dipajang pengepul. Angka ini dihitung otomatis dari data, jadi tidak bisa dipengaruhi rasa sungkan seperti rating bintang.'],
                ],
                'Untuk Pengepul' => [
                    ['Berapa biaya yang harus saya bayar?', 'Komisi '.pengaturan('tarif_komisi', 5).'% dari nilai transaksi yang selesai, dipotong dari saldo. Tidak ada biaya pendaftaran maupun langganan bulanan.'],
                    ['Kenapa harus mengisi saldo lebih dulu?', 'Saldo dipakai untuk membayar komisi. Karena uang transaksi tidak melewati platform, komisi tidak bisa dipotong dari pembayaran. Saldo prabayar juga membuat biaya lebih mudah diprediksi.'],
                    ['Apa yang terjadi kalau saldo saya habis?', 'Kamu tetap bisa menyelesaikan transaksi yang sedang berjalan, tapi tidak bisa menerima permintaan baru sampai saldo diisi kembali.'],
                    ['Apakah harga saya bisa ditolak sistem?', 'Tidak. Kamu bebas menentukan harga berapa pun. Sistem hanya menampilkan perbandingannya terhadap indeks pasar kepada warga.'],
                    ['Apa itu permintaan terbuka?', 'Permintaan dari warga yang tidak menunjuk pengepul tertentu. Permintaan ini terlihat oleh semua pengepul terverifikasi, dan siapa yang mengklaim lebih dulu, dia yang mendapatkannya.'],
                ],
                'Harga dan Indeks' => [
                    ['Dari mana angka Indeks RongsokKu berasal?', 'Dari nilai tengah harga seluruh transaksi yang benar-benar selesai di platform selama 30 hari terakhir. Bukan perkiraan, melainkan harga tempat transaksi sungguhan terjadi.'],
                    ['Kenapa pakai median, bukan rata-rata?', 'Median tahan terhadap angka ekstrem. Satu transaksi dengan harga tidak wajar tidak akan menggeser indeks, sedangkan rata-rata bisa tertarik jauh oleh satu data saja.'],
                    ['Kenapa ada kategori yang tertulis "data belum cukup"?', 'Indeks baru ditampilkan setelah terkumpul minimal '.\App\Models\IndeksHarga::MIN_TRANSAKSI.' transaksi. Di bawah itu angkanya belum bisa dipercaya, jadi lebih baik kami sampaikan apa adanya.'],
                    ['Apakah harga acuan berasal dari pemerintah?', 'Sejauh yang kami ketahui, belum ada lembaga resmi yang menerbitkan harga rongsok secara berkala dan terbuka. Karena itu harga acuan kami susun dari sumber lokal seperti bank sampah induk dan pabrik daur ulang, dan setiap angkanya kami cantumkan sumbernya.'],
                ],
                'Limbah B3' => [
                    ['Apa itu limbah B3?', 'Bahan Berbahaya dan Beracun. Baterai bekas, lampu neon, dan sebagian elektronik mengandung logam berat seperti timbal, kadmium, dan merkuri yang bisa mencemari tanah dan air tanah.'],
                    ['Kenapa tidak semua pengepul menerima B3?', 'Penanganannya butuh prosedur khusus. Hanya pengepul yang menyatakan punya izin B3 yang boleh memasang harga dan menerima kategori ini.'],
                ],
            ];
        @endphp

        @foreach ($tanya as $bagian => $daftar)
            <h2 class="@if (! $loop->first) mt-12 @endif text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $bagian }}
            </h2>

            <div class="mt-4 space-y-3">
                @foreach ($daftar as [$q, $a])
                    {{-- Akordeon memakai elemen HTML asli, tanpa JavaScript. --}}
                    <details class="group rounded-2xl bg-white ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5
                                        font-semibold text-slate-900 dark:text-white">
                            {{ $q }}
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500
                                         transition group-open:rotate-45 dark:bg-slate-800 dark:text-slate-400">
                                <x-ikon nama="tambah" ukuran="size-4" />
                            </span>
                        </summary>
                        <p class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600
                                  dark:border-slate-800 dark:text-slate-400">
                            {{ $a }}
                        </p>
                    </details>
                @endforeach
            </div>
        @endforeach
    </section>
</x-layouts.publik>
