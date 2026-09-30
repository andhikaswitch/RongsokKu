@php
    use App\Enums\StatusPermintaan;
@endphp
<x-layouts.panel :judul="'Permintaan '.$permintaan->kode" :keterangan="$permintaan->status->label()">
    @section('judul', 'Permintaan '.$permintaan->kode)

    @php
        $p = $permintaan;
        $pengepul = $p->pengepul;
        $wa = $pengepul && $p->diterima_pada ? \App\Support\Format::nomorWa($pengepul->user->telepon) : null;
    @endphp

    <x-slot:aksi>
        <x-tombol :href="route('warga.permintaan.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Semua</x-tombol>
    </x-slot:aksi>

    {{-- ══ PANEL TINDAKAN ══ --}}
    @if ($p->status === StatusPermintaan::MenungguKonfirmasi)
        <div class="mb-6 rounded-2xl bg-violet-50 p-5 ring-1 ring-inset ring-violet-600/20 dark:bg-violet-500/10 dark:ring-violet-400/30">
            <div class="flex flex-wrap items-start gap-4">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400">
                    <x-ikon nama="timbangan" ukuran="size-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-bold text-violet-900 dark:text-violet-100">Periksa hasil timbangan</h2>
                    <p class="mt-1 text-sm text-violet-800 dark:text-violet-200">
                        Pengepul menimbang <strong>{{ berat($p->berat_final_kg) }}</strong> dengan total
                        <strong>{{ rupiah($p->total_final) }}</strong>. Konfirmasi hanya setelah uang tunai Anda terima.
                    </p>
                    @if ($p->catatan_pengepul)
                        <p class="mt-2 rounded-lg bg-white/60 p-3 text-sm text-violet-900 dark:bg-slate-900/40 dark:text-violet-100">
                            <strong>Catatan pengepul:</strong> {{ $p->catatan_pengepul }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <x-tombol href="#modal-sengketa" variant="bahaya-halus" ikon="peringatan">Hasil tidak sesuai</x-tombol>
                <x-tombol href="#modal-konfirmasi" variant="primer" ikon="cek">Sesuai, sudah dibayar</x-tombol>
            </div>
        </div>
    @endif

    @if ($p->bisaDiulas())
        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-nilai-50 p-5 ring-1 ring-inset ring-amber-600/20 dark:bg-nilai-500/10 dark:ring-amber-400/30">
            <x-ikon nama="bintang" ukuran="size-6" class="shrink-0 fill-nilai-400 text-nilai-400" />
            <p class="min-w-0 flex-1 text-sm font-medium text-amber-900 dark:text-amber-100">
                Transaksi selesai. Bagaimana layanan {{ $pengepul?->nama_usaha }}?
            </p>
            <x-tombol href="#modal-ulasan" variant="nilai" ukuran="kecil">Beri ulasan</x-tombol>
        </div>
    @endif

    @if ($p->status === StatusPermintaan::Sengketa && $p->sengketa)
        <div class="mb-6 flex items-start gap-4 rounded-2xl bg-rose-50 p-5 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:ring-rose-400/30">
            <x-ikon nama="peringatan" ukuran="size-5" class="mt-0.5 shrink-0 text-rose-600" />
            <div class="text-sm text-rose-800 dark:text-rose-200">
                <p class="font-bold">Keberatan {{ $p->sengketa->kode }} sedang ditinjau admin</p>
                <p class="mt-1">{{ $p->sengketa->alasan }}. Anda akan dihubungi melalui nomor WhatsApp yang terdaftar.</p>
            </div>
        </div>
    @elseif ($p->sengketa?->status === 'selesai')
        <div class="mb-6 rounded-2xl bg-slate-100 p-5 text-sm dark:bg-slate-800/50">
            <p class="font-bold text-slate-900 dark:text-white">Keputusan sengketa {{ $p->sengketa->kode }}</p>
            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $p->sengketa->resolusi }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-kartu judul="Rincian barang">
                @include('partials.permintaan.rincian-barang', ['permintaan' => $p])
            </x-kartu>

            <x-kartu judul="Lokasi penjemputan" padat>
                <x-peta :lat="$p->latitude" :lng="$p->longitude" tinggi="h-52" judul="Lokasi penjemputan" />
                <p class="mt-3 text-sm text-slate-700 dark:text-slate-300">{{ $p->alamat_jemput }}</p>
                <p class="text-xs text-slate-400">{{ $p->wilayah?->namaLengkap() }}</p>
            </x-kartu>

            @if ($p->ulasan)
                <x-kartu judul="Ulasan Anda">
                    <x-bintang :nilai="$p->ulasan->rating" :tampil-angka="false" />
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $p->ulasan->komentar ?: 'Tanpa komentar.' }}</p>
                    @if ($p->ulasan->balasan)
                        <div class="mt-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Balasan pengepul</p>
                            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $p->ulasan->balasan }}</p>
                        </div>
                    @endif
                </x-kartu>
            @endif
        </div>

        <div class="space-y-6">
            <x-kartu judul="Status">
                <div class="mb-5"><x-lencana-status :status="$p->status" /></div>
                @include('partials.permintaan.linimasa', ['permintaan' => $p])
            </x-kartu>

            <x-kartu judul="Pengepul">
                @if ($pengepul)
                    <a href="{{ route('pengepul.detail', $pengepul) }}" class="flex items-center gap-3 hover:opacity-80">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-merk-50 text-merk-600 dark:bg-merk-500/10 dark:text-merk-400">
                            <x-ikon nama="toko" ukuran="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $pengepul->nama_usaha }}</p>
                            <x-bintang :nilai="$pengepul->rating_rata" :jumlah="$pengepul->jumlah_ulasan" ukuran="size-3.5" />
                        </div>
                    </a>
                    @if ($p->jarak_km)
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">± {{ jarak((float) $p->jarak_km) }} dari lapak pengepul</p>
                    @endif
                    @if ($wa)
                        <x-tombol href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo '.$pengepul->nama_usaha.', saya pemilik permintaan '.$p->kode.' di RongsokKu.') }}"
                                  target="_blank" rel="noopener" variant="sekunder" penuh ikon="wa" class="mt-4">
                            Chat WhatsApp
                        </x-tombol>
                    @elseif (! $p->status->selesaiPermanen())
                        <p class="mt-4 text-xs text-slate-400">Kontak WhatsApp muncul setelah pengepul menerima permintaan.</p>
                    @endif
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Permintaan terbuka ini belum diklaim. Pengepul terverifikasi di sekitar Anda bisa melihat dan mengambilnya.
                    </p>
                @endif
            </x-kartu>

            <x-kartu judul="Detail">
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Kode">{{ $p->kode }}</x-baris-info>
                    <x-baris-info label="Dibuat">{{ tanggal_id($p->created_at) }}</x-baris-info>
                    <x-baris-info label="Jadwal">{{ $p->jadwal_tanggal ? tanggal_id($p->jadwal_tanggal).' · '.ucfirst($p->jadwal_sesi) : '-' }}</x-baris-info>
                    @if ($p->catatan_warga)
                        <x-baris-info label="Catatan Anda">{{ $p->catatan_warga }}</x-baris-info>
                    @endif
                </dl>

                @if (in_array($p->status, [StatusPermintaan::Diajukan, StatusPermintaan::Dijadwalkan], true))
                    <x-tombol href="#modal-batal" variant="bahaya-halus" penuh ukuran="kecil" class="mt-4" ikon="silang">
                        Batalkan permintaan
                    </x-tombol>
                @endif
            </x-kartu>
        </div>
    </div>

    {{-- ══ MODAL ══ --}}
    <x-modal id="modal-batal" judul="Batalkan permintaan?" keterangan="Pengepul akan melihat permintaan ini dibatalkan.">
        <form method="POST" action="{{ route('warga.permintaan.batal', $p) }}" class="space-y-4">
            @csrf
            <x-kolom nama="alasan" label="Alasan (opsional)" placeholder="Contoh: barang sudah dijual" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Tidak jadi</x-tombol>
                <x-tombol type="submit" variant="bahaya">Ya, batalkan</x-tombol>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-konfirmasi" judul="Konfirmasi transaksi selesai?">
        <form method="POST" action="{{ route('warga.permintaan.konfirmasi', $p) }}" class="space-y-4">
            @csrf
            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Anda menyatakan telah menerima pembayaran tunai sebesar
                <strong class="text-slate-900 dark:text-white">{{ rupiah($p->total_final) }}</strong>
                untuk {{ berat($p->berat_final_kg) }} rongsok. Tindakan ini tidak bisa dibatalkan.
            </p>
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Periksa lagi</x-tombol>
                <x-tombol type="submit" variant="primer" ikon="cek">Ya, sudah saya terima</x-tombol>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-sengketa" judul="Ajukan keberatan" keterangan="Admin akan meninjau dan menengahi." lebar="max-w-xl">
        <form method="POST" action="{{ route('warga.permintaan.sengketa', $p) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-pilihan nama="alasan" label="Masalahnya" wajib :kosong="false" :opsi="[
                'Harga diturunkan sepihak di lokasi' => 'Harga diturunkan sepihak di lokasi',
                'Berat timbangan tidak sesuai' => 'Berat timbangan tidak sesuai',
                'Uang belum dibayar penuh' => 'Uang belum dibayar penuh',
                'Lainnya' => 'Lainnya',
            ]" />
            <x-kolom nama="deskripsi" label="Ceritakan kejadiannya" tipe="textarea" rows="4" wajib
                     placeholder="Minimal 20 karakter. Contoh: di aplikasi Rp 2.200/kg, tapi saat di rumah pengepul hanya mau bayar Rp 1.600/kg." />
            <x-unggah nama="bukti" label="Foto bukti (opsional)" bantuan="Misalnya foto layar timbangan. JPG/PNG, maks. 2 MB." />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                <x-tombol type="submit" variant="bahaya" ikon="peringatan">Kirim keberatan</x-tombol>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-ulasan" judul="Beri ulasan" :keterangan="'Untuk '.($pengepul?->nama_usaha ?? '')">
        <form method="POST" action="{{ route('warga.permintaan.ulasan', $p) }}" class="space-y-5">
            @csrf
            <fieldset>
                <legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Rating layanan</legend>
                {{-- Bintang dengan radio + peer: tanpa JavaScript. --}}
                <div class="flex flex-row-reverse justify-end gap-1">
                    @for ($b = 5; $b >= 1; $b--)
                        <input type="radio" name="rating" id="rating-{{ $b }}" value="{{ $b }}" class="peer sr-only" @checked(old('rating', 5) == $b)>
                        <label for="rating-{{ $b }}" title="{{ $b }} bintang"
                               class="cursor-pointer text-slate-300 transition hover:text-nilai-400 peer-checked:text-nilai-400 peer-hover:text-nilai-400 [&:hover~label]:text-nilai-400 dark:text-slate-600">
                            <x-ikon nama="bintang" ukuran="size-9" class="fill-current" />
                        </label>
                    @endfor
                </div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Nilai mutu layanannya: ketepatan waktu, keramahan, kejujuran timbangan.
                    Soal harga sudah diukur otomatis lewat skor kepatuhan harga.
                </p>
            </fieldset>
            <x-kolom nama="komentar" label="Komentar (opsional)" tipe="textarea" rows="3" placeholder="Ceritakan pengalaman Anda" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Nanti</x-tombol>
                <x-tombol type="submit" variant="nilai" ikon="bintang">Kirim ulasan</x-tombol>
            </div>
        </form>
    </x-modal>
</x-layouts.panel>
