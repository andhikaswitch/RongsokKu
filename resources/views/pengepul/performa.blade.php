<x-layouts.panel judul="Performa Saya" keterangan="Semua angka dihitung otomatis dari data transaksi">
    @section('judul', 'Performa Pengepul')

    @php
        $mutu = $profil->mutuKepatuhan();
        $totalUlasan = max(1, $profil->jumlah_ulasan);
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-statistik label="Kepatuhan Harga"
                     :nilai="rtrim(rtrim(number_format((float) $profil->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',').'%'"
                     ikon="perisai" :warna="$mutu['warna'] === 'rose' ? 'rose' : 'merk'"
                     :keterangan="$mutu['label']" />
        <x-statistik label="Tingkat Penerimaan"
                     :nilai="rtrim(rtrim(number_format((float) $profil->tingkat_penerimaan, 1, ',', '.'), '0'), ',').'%'"
                     ikon="lonceng" warna="sky" />
        <x-statistik label="Ketepatan Waktu"
                     :nilai="rtrim(rtrim(number_format((float) $profil->ketepatan_waktu, 1, ',', '.'), '0'), ',').'%'"
                     ikon="jam" warna="nilai" />
        <x-statistik label="Pembatalan Sepihak" :nilai="$profil->pembatalan_sepihak.' kali'"
                     ikon="silang" :warna="$profil->pembatalan_sepihak > 0 ? 'rose' : 'violet'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-kartu judul="Apa arti angka-angka ini?">
            <dl class="space-y-5 text-sm">
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">Kepatuhan Harga</dt>
                    <dd class="mt-1 leading-relaxed text-slate-600 dark:text-slate-400">
                        Persentase transaksi yang Anda bayar sesuai atau di atas harga yang tertera
                        di daftar harga saat warga mengajukan. Harga dibekukan ketika permintaan dibuat,
                        jadi perubahan daftar harga setelah itu tidak dihitung sebagai pelanggaran.
                        <strong class="font-semibold text-slate-900 dark:text-white">Inilah angka yang
                        paling diperhatikan warga sebelum memilih pengepul.</strong>
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">Tingkat Penerimaan</dt>
                    <dd class="mt-1 leading-relaxed text-slate-600 dark:text-slate-400">
                        Berapa persen permintaan yang masuk Anda terima, bukan tolak. Sering menolak
                        membuat warga enggan memilih lapak Anda.
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">Ketepatan Waktu</dt>
                    <dd class="mt-1 leading-relaxed text-slate-600 dark:text-slate-400">
                        Berapa persen penjemputan yang Anda lakukan pada tanggal yang dijadwalkan.
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">Rating bintang</dt>
                    <dd class="mt-1 leading-relaxed text-slate-600 dark:text-slate-400">
                        Murni penilaian warga atas mutu layanan Anda. Rating
                        <strong class="font-semibold text-slate-900 dark:text-white">tidak pernah dikurangi
                        secara otomatis karena harga</strong>, karena harga rendah adalah pilihan bisnis
                        yang sah, bukan pelanggaran.
                    </dd>
                </div>
            </dl>
        </x-kartu>

        <x-kartu judul="Ulasan Warga" :keterangan="$profil->jumlah_ulasan.' ulasan diterima'">
            <div class="flex items-center gap-5">
                <div class="text-center">
                    <p class="text-4xl font-extrabold text-slate-900 tabular-nums dark:text-white">
                        {{ number_format((float) $profil->rating_rata, 1, ',', '.') }}
                    </p>
                    <x-bintang :nilai="$profil->rating_rata" ukuran="size-4" :tampil-angka="false" class="mt-1" />
                </div>

                <div class="flex-1 space-y-1.5">
                    @for ($b = 5; $b >= 1; $b--)
                        @php $jml = $sebaranRating[$b] ?? 0; @endphp
                        <div class="flex items-center gap-2">
                            <span class="w-3 text-xs text-slate-400">{{ $b }}</span>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-nilai-400"
                                     style="width: {{ round($jml / $totalUlasan * 100) }}%"></div>
                            </div>
                            <span class="w-6 text-right text-xs tabular-nums text-slate-400">{{ $jml }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="mt-6 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($ulasan as $u)
                    <div class="py-3.5 first:pt-0">
                        <div class="flex items-center gap-2">
                            <x-avatar :nama="$u->warga->name" ukuran="size-7" />
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $u->warga->name }}</span>
                            <x-bintang :nilai="$u->rating" ukuran="size-3" :tampil-angka="false" />
                            <span class="ml-auto text-xs text-slate-400">{{ tanggal_id($u->created_at) }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $u->komentar }}</p>
                    </div>
                @empty
                    <x-kosong ikon="bintang" judul="Belum ada ulasan"
                              pesan="Ulasan muncul setelah warga menilai transaksi yang selesai." />
                @endforelse
            </div>
        </x-kartu>
    </div>
</x-layouts.panel>
