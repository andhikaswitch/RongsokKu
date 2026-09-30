@php
    use App\Enums\StatusPermintaan;
    use App\Support\OsmEmbed;
@endphp
<x-layouts.panel :judul="'Permintaan '.$permintaan->kode" :keterangan="$permintaan->warga->name">
    @section('judul', 'Permintaan '.$permintaan->kode)

    @php
        $p = $permintaan;
        $warga = $p->warga;
        $wa = $p->diterima_pada ? \App\Support\Format::nomorWa($warga->telepon) : null;
        $rute = OsmEmbed::rute(
            (float) auth()->user()->latitude, (float) auth()->user()->longitude,
            (float) $p->latitude, (float) $p->longitude
        );
    @endphp

    <x-slot:aksi>
        <x-tombol :href="route('pengepul.permintaan.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Semua</x-tombol>
    </x-slot:aksi>

    {{-- ══ PANEL TINDAKAN SESUAI STATUS ══ --}}
    @switch($p->status)
        @case(StatusPermintaan::Diajukan)
            <x-kartu judul="Permintaan baru — terima atau tolak?" class="mb-6 ring-amber-300 dark:ring-amber-500/40">
                <form method="POST" action="{{ route('pengepul.permintaan.terima', $p) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Warga mengusulkan <strong class="text-slate-900 dark:text-white">{{ tanggal_id($p->jadwal_tanggal) }}, sesi {{ $p->jadwal_sesi }}</strong>.
                        Ubah bila Anda tidak bisa datang di jadwal itu.
                    </p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-kolom nama="jadwal_tanggal" label="Tanggal jemput" tipe="date" :nilai="$p->jadwal_tanggal?->toDateString()" :min="now()->toDateString()" />
                        <x-pilihan nama="jadwal_sesi" label="Sesi" :nilai="$p->jadwal_sesi" :kosong="false"
                                   :opsi="['pagi' => 'Pagi (07–11)', 'siang' => 'Siang (11–14)', 'sore' => 'Sore (14–17)']" />
                        <x-kolom nama="catatan_pengepul" label="Pesan untuk warga" placeholder="Opsional" />
                    </div>
                    <div class="flex flex-wrap justify-end gap-2">
                        <x-tombol href="#modal-tolak" variant="bahaya-halus" ikon="silang">Tolak</x-tombol>
                        <x-tombol type="submit" variant="primer" ikon="cek">Terima & jadwalkan</x-tombol>
                    </div>
                </form>
            </x-kartu>
            @break

        @case(StatusPermintaan::Dijadwalkan)
            <div class="mb-6 flex flex-wrap items-center gap-4 rounded-2xl bg-sky-50 p-5 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:ring-sky-400/30">
                <x-ikon nama="kalender" ukuran="size-6" class="shrink-0 text-sky-600" />
                <p class="min-w-0 flex-1 text-sm text-sky-900 dark:text-sky-100">
                    Jadwal jemput <strong>{{ tanggal_id($p->jadwal_tanggal) }}, sesi {{ $p->jadwal_sesi }}</strong>.
                    Tekan tombol saat Anda berangkat agar warga tahu.
                </p>
                <div class="flex gap-2">
                    <x-tombol href="#modal-batal" variant="hantu" ukuran="kecil">Batalkan</x-tombol>
                    <form method="POST" action="{{ route('pengepul.permintaan.berangkat', $p) }}">
                        @csrf
                        <x-tombol type="submit" variant="primer" ikon="truk">Saya berangkat</x-tombol>
                    </form>
                </div>
            </div>
            @break

        @case(StatusPermintaan::Dijemput)
            <x-kartu judul="Masukkan hasil timbangan" keterangan="Timbang di depan warga, lalu isi berat aktual tiap barang." class="mb-6 ring-indigo-300 dark:ring-indigo-500/40">
                <form method="POST" action="{{ route('pengepul.permintaan.timbang', $p) }}" class="space-y-4">
                    @csrf
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($p->item as $item)
                            <div class="grid gap-3 py-3 sm:grid-cols-5 sm:items-end">
                                <div class="sm:col-span-2">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $item->kategori->ikon }} {{ $item->kategori->nama }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Taksiran warga {{ berat($item->estimasi_berat) }} · harga disepakati {{ rupiah($item->harga_estimasi_per_satuan) }}/{{ $item->kategori->satuan }}
                                    </p>
                                </div>
                                <x-kolom :nama="'hasil['.$item->id.'][berat_final]'" label="Berat aktual" tipe="number" step="0.01" min="0"
                                         akhiran="kg" wajib :nilai="old('hasil.'.$item->id.'.berat_final', (float) $item->estimasi_berat)" class="sm:col-span-1" />
                                <div class="sm:col-span-2">
                                    <x-kolom :nama="'hasil['.$item->id.'][harga_final]'" label="Harga per kg" tipe="number" step="25" min="0"
                                             awalan="Rp" wajib :nilai="old('hasil.'.$item->id.'.harga_final', (float) $item->harga_estimasi_per_satuan)" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-900 dark:bg-amber-500/10 dark:text-amber-100">
                        <strong>Perhatian:</strong> menurunkan harga di bawah yang disepakati wajib disertai alasan,
                        akan terlihat oleh warga, dan menurunkan skor kepatuhan harga Anda. Warga juga berhak menolak dan mengajukan sengketa.
                    </div>

                    <x-kolom nama="catatan_pengepul" label="Catatan / alasan (wajib bila harga diturunkan)" tipe="textarea" rows="2"
                             placeholder="Contoh: sebagian kardus basah sehingga dihitung terpisah" />

                    <div class="flex flex-wrap justify-end gap-2">
                        <x-tombol href="#modal-batal" variant="hantu">Batalkan</x-tombol>
                        <x-tombol type="submit" variant="primer" ikon="timbangan">Simpan hasil timbangan</x-tombol>
                    </div>
                </form>
            </x-kartu>
            @break

        @case(StatusPermintaan::MenungguKonfirmasi)
            <div class="mb-6 flex items-start gap-4 rounded-2xl bg-violet-50 p-5 ring-1 ring-inset ring-violet-600/20 dark:bg-violet-500/10 dark:ring-violet-400/30">
                <x-ikon nama="jam" ukuran="size-6" class="shrink-0 text-violet-600" />
                <p class="text-sm text-violet-900 dark:text-violet-100">
                    Bayar tunai <strong>{{ rupiah($p->total_final) }}</strong> kepada warga, lalu minta warga menekan
                    konfirmasi di aplikasinya. Komisi {{ pengaturan('tarif_komisi', 5) }}%
                    (± {{ rupiah(round((float) $p->total_final * (float) pengaturan('tarif_komisi', 5) / 100)) }})
                    dipotong dari saldo setelah dikonfirmasi.
                </p>
            </div>
            @break

        @case(StatusPermintaan::Selesai)
            <div class="mb-6 flex items-start gap-4 rounded-2xl bg-emerald-50 p-5 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:ring-emerald-400/30">
                <x-ikon nama="cek-lingkar" ukuran="size-6" class="shrink-0 text-emerald-600" />
                <p class="text-sm text-emerald-900 dark:text-emerald-100">
                    Transaksi selesai {{ tanggal_id($p->selesai_pada) }}. Komisi {{ rtrim(rtrim(number_format((float) $p->tarif_komisi, 2, ',', '.'), '0'), ',') }}%
                    sebesar <strong>{{ rupiah($p->jumlah_komisi) }}</strong> sudah dipotong dari saldo.
                </p>
            </div>
            @break
    @endswitch

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-kartu judul="Rincian barang">
                @include('partials.permintaan.rincian-barang', ['permintaan' => $p])
            </x-kartu>

            <x-kartu judul="Lokasi penjemputan" padat>
                <x-peta :lat="$p->latitude" :lng="$p->longitude" tinggi="h-60" judul="Lokasi penjemputan" />
                <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $p->alamat_jemput }}</p>
                        <p class="text-xs text-slate-400">{{ $p->wilayah?->namaLengkap() }}{{ $p->jarak_km ? ' · ± '.jarak((float) $p->jarak_km).' dari lapak' : '' }}</p>
                    </div>
                    @if ($rute && ! $p->status->selesaiPermanen())
                        <x-tombol :href="$rute" target="_blank" rel="noopener" variant="sekunder" ukuran="kecil" ikon="peta">Petunjuk arah</x-tombol>
                    @endif
                </div>
            </x-kartu>

            @if ($p->ulasan)
                <x-kartu judul="Ulasan warga">
                    <x-bintang :nilai="$p->ulasan->rating" :tampil-angka="false" />
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $p->ulasan->komentar ?: 'Tanpa komentar.' }}</p>
                    <x-tombol :href="route('pengepul.performa')" variant="hantu" ukuran="kecil" class="mt-3" ikon-kanan="panah-kanan">
                        {{ $p->ulasan->balasan ? 'Lihat balasan' : 'Balas di halaman ulasan' }}
                    </x-tombol>
                </x-kartu>
            @endif
        </div>

        <div class="space-y-6">
            <x-kartu judul="Status">
                <div class="mb-5"><x-lencana-status :status="$p->status" /></div>
                @include('partials.permintaan.linimasa', ['permintaan' => $p])
            </x-kartu>

            <x-kartu judul="Warga">
                <div class="flex items-center gap-3">
                    <x-avatar :nama="$warga->name" :foto="$warga->foto_profil" ukuran="size-11" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $warga->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $warga->wilayah?->namaLengkap() }}</p>
                    </div>
                </div>
                @if ($p->catatan_warga)
                    <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-800/50 dark:text-slate-400">
                        “{{ $p->catatan_warga }}”
                    </p>
                @endif
                @if ($wa && ! $p->status->selesaiPermanen())
                    <x-tombol href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo '.$warga->name.', saya dari '.$p->pengepul?->nama_usaha.' terkait penjemputan '.$p->kode.' di RongsokKu.') }}"
                              target="_blank" rel="noopener" variant="sekunder" penuh ikon="wa" class="mt-4">
                        Chat WhatsApp
                    </x-tombol>
                @elseif (! $p->diterima_pada)
                    <p class="mt-4 text-xs text-slate-400">Kontak warga terbuka setelah Anda menerima permintaan.</p>
                @endif
            </x-kartu>
        </div>
    </div>

    <x-modal id="modal-tolak" judul="Tolak permintaan?" keterangan="Warga akan melihat alasan Anda.">
        <form method="POST" action="{{ route('pengepul.permintaan.tolak', $p) }}" class="space-y-4">
            @csrf
            <x-pilihan nama="alasan" label="Alasan" wajib :kosong="false" :opsi="[
                'Di luar jangkauan layanan' => 'Di luar jangkauan layanan',
                'Jadwal penuh' => 'Jadwal penuh',
                'Jumlah barang terlalu sedikit' => 'Jumlah barang terlalu sedikit',
                'Sedang tidak menerima jenis barang ini' => 'Sedang tidak menerima jenis barang ini',
            ]" />
            <p class="text-xs text-slate-500 dark:text-slate-400">Terlalu sering menolak menurunkan tingkat penerimaan Anda.</p>
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                <x-tombol type="submit" variant="bahaya">Tolak permintaan</x-tombol>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-batal" judul="Batalkan penjemputan?" keterangan="Pembatalan sepihak tercatat di profil publik Anda.">
        <form method="POST" action="{{ route('pengepul.permintaan.batal', $p) }}" class="space-y-4">
            @csrf
            <x-kolom nama="alasan" label="Alasan" wajib placeholder="Contoh: kendaraan rusak" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Tidak jadi</x-tombol>
                <x-tombol type="submit" variant="bahaya">Batalkan</x-tombol>
            </div>
        </form>
    </x-modal>
</x-layouts.panel>
