@php
    use App\Enums\StatusVerifikasi;
@endphp
<x-layouts.panel judul="Verifikasi Lapak" keterangan="Wajib sebelum lapak tampil di pencarian warga">
    @section('judul', 'Verifikasi Lapak')

    
    <div class="grid max-w-5xl gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($profil->status_verifikasi === StatusVerifikasi::Ditolak)
                <div class="mb-6 flex items-start gap-3 rounded-2xl bg-rose-50 p-5 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:ring-rose-400/30">
                    <x-ikon nama="silang" ukuran="size-5" class="mt-0.5 shrink-0 text-rose-600" />
                    <div class="text-sm text-rose-800 dark:text-rose-200">
                        <p class="font-bold">Pengajuan sebelumnya ditolak</p>
                        <p class="mt-1">Alasan: {{ $profil->alasan_penolakan }}. Perbaiki berkas lalu ajukan ulang.</p>
                    </div>
                </div>
            @endif

            <x-kartu judul="Berkas verifikasi">
                <form method="POST" action="{{ route('pengepul.verifikasi.ajukan') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <x-kolom nama="nama_usaha" label="Nama usaha / lapak" wajib :nilai="$profil->nama_usaha" />

                    <x-kolom nama="deskripsi" label="Deskripsi lapak" tipe="textarea" rows="3" :nilai="$profil->deskripsi"
                             placeholder="Contoh: melayani penjemputan di Karawang Barat, timbangan digital, bayar tunai." />

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-unggah nama="foto_ktp" label="Foto KTP pemilik" :wajib="! $profil->foto_ktp"
                                  :sudah-ada="$profil->foto_ktp ? 'KTP sudah diunggah; unggah lagi untuk mengganti' : null"
                                  bantuan="Hanya bisa dilihat admin. JPG/PNG, maks. 2 MB." />
                        <x-unggah nama="foto_lapak" label="Foto lapak / gudang" :wajib="! $profil->foto_lapak"
                                  :sudah-ada="$profil->foto_lapak ? 'Foto lapak sudah diunggah' : null"
                                  bantuan="Tampil di profil publik Anda." />
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                        <input type="hidden" name="izin_b3" value="0">
                        <input type="checkbox" name="izin_b3" value="1" @checked(old('izin_b3', $profil->izin_b3))
                               class="mt-0.5 size-4 rounded border-slate-300 text-merk-600 focus:ring-merk-500">
                        <span class="text-sm">
                            <span class="font-semibold text-slate-900 dark:text-white">Saya berizin menangani limbah B3</span>
                            <span class="mt-0.5 block text-slate-500 dark:text-slate-400">
                                Centang hanya bila lapak Anda memiliki izin pengelolaan limbah Bahan Berbahaya dan Beracun
                                (baterai, lampu neon, dll). Admin dapat meminta bukti izin.
                            </span>
                        </span>
                    </label>

                    <div class="flex justify-end">
                        <x-tombol type="submit" variant="primer" ikon="unggah">Ajukan verifikasi</x-tombol>
                    </div>
                </form>
            </x-kartu>
        </div>

        <x-kartu judul="Kenapa perlu verifikasi?">
            <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-400">
                <li class="flex gap-3">
                    <x-ikon nama="perisai" ukuran="size-5 shrink-0 text-merk-600" />
                    Warga akan mengundang Anda ke rumahnya. Verifikasi identitas membangun kepercayaan itu.
                </li>
                <li class="flex gap-3">
                    <x-ikon nama="cari" ukuran="size-5 shrink-0 text-merk-600" />
                    Hanya lapak terverifikasi yang muncul di hasil pencarian dan bisa menerima permintaan.
                </li>
                <li class="flex gap-3">
                    <x-ikon nama="jam" ukuran="size-5 shrink-0 text-merk-600" />
                    Admin memeriksa berkas dalam 1–2 hari kerja. Sambil menunggu, Anda bisa mengisi saldo.
                </li>
            </ul>
        </x-kartu>
    </div>
</x-layouts.panel>
