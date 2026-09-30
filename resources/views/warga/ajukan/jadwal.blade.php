<x-layouts.panel judul="Ajukan Penjemputan" keterangan="Langkah 3 dari 3 · Alamat & jadwal">
    @section('judul', 'Alamat & Jadwal')

    @php $pengguna = auth()->user(); @endphp

    <x-langkah :aktif="3" class="mb-8 max-w-2xl" />

    <form method="POST" action="{{ route('warga.ajukan.kirim') }}" class="grid gap-6 lg:grid-cols-5">
        @csrf

        <div class="space-y-6 lg:col-span-3">
            <x-kartu judul="Alamat penjemputan">
                <div class="space-y-5">
                    <x-pilihan nama="wilayah_id" label="Kelurahan" wajib :nilai="$pengguna->wilayah_id" kosong="Pilih kelurahan">
                        @foreach ($kelurahan as $namaKecamatan => $daftar)
                            <optgroup label="Kec. {{ $namaKecamatan }}">
                                @foreach ($daftar as $w)
                                    <option value="{{ $w->id }}" @selected(old('wilayah_id', $pengguna->wilayah_id) == $w->id)>{{ $w->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-pilihan>

                    <x-kolom nama="alamat_jemput" label="Alamat lengkap" tipe="textarea" rows="2" wajib
                             :nilai="$pengguna->alamat_detail"
                             placeholder="Nama jalan, nomor rumah, RT/RW, patokan" />
                </div>
            </x-kartu>

            <x-kartu judul="Jadwal penjemputan" keterangan="Pengepul bisa mengusulkan jadwal lain saat menerima.">
                <div class="space-y-5">
                    <x-kolom nama="jadwal_tanggal" label="Tanggal" tipe="date" wajib
                             :nilai="now()->addDay()->toDateString()"
                             :min="now()->addDay()->toDateString()"
                             :max="now()->addDays(30)->toDateString()" />

                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                            Sesi <span class="text-rose-500">*</span>
                        </legend>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['pagi' => ['Pagi', '07.00–11.00'], 'siang' => ['Siang', '11.00–14.00'], 'sore' => ['Sore', '14.00–17.00']] as $nilai => [$label, $jam])
                                <label class="cursor-pointer">
                                    <input type="radio" name="jadwal_sesi" value="{{ $nilai }}" class="peer sr-only"
                                           @checked(old('jadwal_sesi', 'pagi') === $nilai)>
                                    <span class="flex flex-col items-center rounded-xl bg-white px-3 py-3 text-center ring-1 ring-slate-200 transition
                                                 peer-checked:bg-merk-50 peer-checked:ring-2 peer-checked:ring-merk-600
                                                 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-merk-600
                                                 dark:bg-slate-800 dark:ring-slate-700 dark:peer-checked:bg-merk-500/10 dark:peer-checked:ring-merk-400">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $label }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $jam }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <x-kolom nama="catatan_warga" label="Catatan untuk pengepul (opsional)" tipe="textarea" rows="2"
                             placeholder="Contoh: rumah pagar hijau, telepon dulu sebelum datang" />
                </div>
            </x-kartu>
        </div>

        <div class="lg:col-span-2">
            <x-kartu judul="Ringkasan" class="lg:sticky lg:top-20">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dijemput oleh</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ $terbuka ? 'Pengepul pertama yang mengklaim' : $pengepul->nama_usaha }}
                </p>

                <div class="mt-5 divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    @foreach ($rincian as $r)
                        <div class="flex justify-between gap-3 py-2.5 text-sm">
                            <span class="text-slate-600 dark:text-slate-400">{{ $r->kategori?->ikon }} {{ $r->kategori?->nama }} · {{ berat($r->berat) }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ rupiah($r->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex items-baseline justify-between border-t border-slate-200 pt-4 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Perkiraan diterima</span>
                    <span class="text-2xl font-extrabold tabular-nums text-merk-700 dark:text-merk-400">{{ rupiah($rincian->sum('subtotal')) }}</span>
                </div>

                <ul class="mt-5 space-y-2 rounded-xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-600 dark:bg-slate-800/50 dark:text-slate-400">
                    @unless ($terbuka)
                        <li class="flex gap-2"><x-ikon nama="perisai" ukuran="size-4 shrink-0 text-merk-600" /> Harga per kg dikunci saat Anda menekan kirim.</li>
                    @endunless
                    <li class="flex gap-2"><x-ikon nama="timbangan" ukuran="size-4 shrink-0 text-merk-600" /> Yang dibayar adalah berat hasil timbangan di lokasi.</li>
                    <li class="flex gap-2"><x-ikon nama="dompet" ukuran="size-4 shrink-0 text-merk-600" /> Dibayar tunai oleh pengepul. Gratis tanpa potongan.</li>
                </ul>

                <x-slot:kaki>
                    <div class="flex items-center justify-between gap-3">
                        <x-tombol :href="route('warga.ajukan.barang')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Kembali</x-tombol>
                        <x-tombol type="submit" variant="primer" ikon="truk">Kirim Permintaan</x-tombol>
                    </div>
                </x-slot:kaki>
            </x-kartu>
        </div>
    </form>
</x-layouts.panel>
