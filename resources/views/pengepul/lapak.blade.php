<x-layouts.panel judul="Profil Lapak" :keterangan="$profil->nama_usaha">
    @section('judul', 'Profil Lapak')

    <x-slot:aksi>
        @if ($profil->terverifikasi())
            <x-tombol :href="route('pengepul.detail', $profil)" target="_blank" variant="sekunder" ukuran="kecil" ikon="toko">Lihat halaman publik</x-tombol>
        @endif
    </x-slot:aksi>

    <div class="grid max-w-5xl gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-kartu judul="Informasi lapak">
                <form method="POST" action="{{ route('pengepul.profil.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                        <span>
                            <span class="block font-semibold text-slate-900 dark:text-white">Sedang menerima permintaan</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Matikan saat libur agar warga tidak menunggu.</span>
                        </span>
                        {{-- Sakelar murni CSS: checkbox + peer. --}}
                        <span class="relative inline-flex shrink-0">
                            <input type="hidden" name="sedang_menerima" value="0">
                            <input type="checkbox" name="sedang_menerima" value="1" class="peer sr-only" @checked(old('sedang_menerima', $profil->sedang_menerima))>
                            <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-merk-600 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-merk-600 dark:bg-slate-600"></span>
                            <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                        </span>
                    </label>

                    <x-kolom nama="nama_usaha" label="Nama usaha" wajib :nilai="$profil->nama_usaha" />
                    <x-kolom nama="deskripsi" label="Deskripsi" tipe="textarea" rows="3" :nilai="$profil->deskripsi" />

                    <div class="grid gap-5 sm:grid-cols-3">
                        <x-kolom nama="jam_buka" label="Jam buka" tipe="time" wajib :nilai="\Illuminate\Support\Str::substr($profil->jam_buka, 0, 5)" />
                        <x-kolom nama="jam_tutup" label="Jam tutup" tipe="time" wajib :nilai="\Illuminate\Support\Str::substr($profil->jam_tutup, 0, 5)" />
                        <x-kolom nama="radius_layanan_km" label="Radius layanan" tipe="number" min="1" max="50" akhiran="km" wajib :nilai="$profil->radius_layanan_km" />
                    </div>

                    <div class="flex items-center gap-4">
                        @if ($profil->foto_lapak)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($profil->foto_lapak) }}" alt="Foto lapak"
                                 class="size-20 shrink-0 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                        @endif
                        <div class="flex-1"><x-unggah nama="foto_lapak" label="Foto lapak" /></div>
                    </div>

                    <div class="flex justify-end">
                        <x-tombol type="submit" variant="primer" ikon="cek">Simpan</x-tombol>
                    </div>
                </form>
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Verifikasi">
                <x-lencana-status :status="$profil->status_verifikasi" />
                <dl class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Izin B3">{{ $profil->izin_b3 ? 'Ya' : 'Tidak' }}</x-baris-info>
                    @if ($profil->diverifikasi_pada)
                        <x-baris-info label="Sejak">{{ tanggal_id($profil->diverifikasi_pada) }}</x-baris-info>
                    @endif
                </dl>
                @unless ($profil->terverifikasi())
                    <x-tombol :href="route('pengepul.verifikasi.status')" variant="sekunder" penuh ukuran="kecil" class="mt-4">Lihat status</x-tombol>
                @endunless
            </x-kartu>

            <x-kartu judul="Lokasi lapak" padat>
                <x-peta :lat="$profil->user->latitude" :lng="$profil->user->longitude" tinggi="h-44" />
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ $profil->user->alamat_detail }}</p>
                <x-tombol :href="route('pengepul.akun')" variant="hantu" ukuran="kecil" class="mt-2" ikon="pensil">Ubah alamat di Akun Saya</x-tombol>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
