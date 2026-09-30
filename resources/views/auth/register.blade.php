<x-layouts.auth judul="Buat Akun RongsokKu"
                keterangan="Gratis selamanya untuk warga. Pengepul hanya dikenakan komisi saat transaksi berhasil.">
    @section('judul', 'Daftar')

    @php $peranTerpilih = old('peran', $peran); @endphp

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Pemilih peran: radio + peer, tanpa JavaScript. --}}
        <fieldset>
            <legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                Saya mendaftar sebagai
            </legend>

            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['warga', 'pengguna', 'Warga', 'Punya rongsok untuk dijual'],
                    ['pengepul', 'toko', 'Pengepul', 'Membeli rongsok dari warga'],
                ] as [$nilai, $ikon, $label, $keterangan])
                    <label class="relative cursor-pointer">
                        <input type="radio" name="peran" value="{{ $nilai }}"
                               @checked($peranTerpilih === $nilai)
                               class="peer sr-only">
                        <span class="flex h-full flex-col items-center gap-2 rounded-2xl bg-white p-4 text-center
                                     ring-1 ring-slate-200 transition
                                     peer-checked:bg-merk-50 peer-checked:ring-2 peer-checked:ring-merk-600
                                     peer-focus-visible:outline peer-focus-visible:outline-2
                                     peer-focus-visible:outline-offset-2 peer-focus-visible:outline-merk-600
                                     dark:bg-slate-800 dark:ring-slate-700
                                     dark:peer-checked:bg-merk-500/10 dark:peer-checked:ring-merk-400">
                            <x-ikon :nama="$ikon" ukuran="size-6" class="text-slate-400 peer-checked:text-merk-600" />
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $label }}</span>
                            <span class="text-xs leading-snug text-slate-500 dark:text-slate-400">{{ $keterangan }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{--
            Kolom nama usaha selalu dirender karena tanpa JavaScript kita tidak
            bisa menyembunyikannya secara dinamis. Validasi required_if di server
            yang menentukan wajib tidaknya.
        --}}
        <x-kolom label="Nama Usaha / Lapak" nama="nama_usaha"
                 placeholder="Contoh: Pengepul Jaya"
                 bantuan="Wajib diisi bila Anda mendaftar sebagai pengepul." />

        <x-kolom label="Nama Lengkap" nama="name" wajib autocomplete="name"
                 placeholder="Nama sesuai KTP" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-kolom label="Alamat Email" nama="email" tipe="email" wajib
                     autocomplete="email" placeholder="nama@email.com" />

            <x-kolom label="Nomor WhatsApp" nama="telepon" wajib
                     autocomplete="tel" placeholder="08xxxxxxxxxx" />
        </div>

        <x-pilihan label="Kelurahan" nama="wilayah_id" wajib
                   kosong="Pilih kelurahan Anda"
                   bantuan="Dipakai untuk menghitung jarak ke pengepul terdekat.">
            @foreach ($kelurahan as $namaKecamatan => $daftar)
                <optgroup label="Kec. {{ $namaKecamatan }}">
                    @foreach ($daftar as $w)
                        <option value="{{ $w->id }}" @selected(old('wilayah_id') == $w->id)>{{ $w->nama }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </x-pilihan>

        <x-kolom label="Alamat Lengkap" nama="alamat_detail" tipe="textarea" wajib
                 placeholder="Nama jalan, nomor rumah, RT/RW, patokan" rows="2" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-kolom label="Kata Sandi" nama="password" tipe="password" wajib
                     autocomplete="new-password" placeholder="Minimal 8 karakter" />

            <x-kolom label="Ulangi Kata Sandi" nama="password_confirmation" tipe="password" wajib
                     autocomplete="new-password" placeholder="Ketik ulang" />
        </div>

        <x-tombol type="submit" variant="primer" ukuran="besar" penuh ikon="cek">
            Buat Akun
        </x-tombol>

        <p class="text-center text-xs leading-relaxed text-slate-500 dark:text-slate-400">
            Dengan mendaftar, Anda menyetujui bahwa transaksi dibayar tunai langsung
            oleh pengepul di lokasi penjemputan.
        </p>
    </form>

    <x-slot:kaki>
        <span class="text-slate-500 dark:text-slate-400">Sudah punya akun?</span>
        <a href="{{ route('login') }}" class="font-semibold text-merk-700 hover:text-merk-800 dark:text-merk-400 dark:hover:text-merk-300">
            Masuk di sini
        </a>
    </x-slot:kaki>
</x-layouts.auth>
