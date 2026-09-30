@php
    $awalan = $pengguna->isPengepul() ? 'pengepul.akun' : 'warga.profil';
@endphp

<x-layouts.panel judul="Akun Saya" keterangan="Data diri dan keamanan akun">
    @section('judul', 'Akun Saya')

    <div class="grid max-w-5xl gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-kartu judul="Data diri">
                <form method="POST" action="{{ route($awalan.'.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-4">
                        <x-avatar :nama="$pengguna->name" :foto="$pengguna->foto_profil" ukuran="size-16" />
                        <div class="min-w-0 flex-1">
                            <x-unggah nama="foto_profil" label="Foto profil" />
                        </div>
                    </div>

                    <x-kolom nama="name" label="Nama lengkap" wajib :nilai="$pengguna->name" />

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-kolom nama="email_tampil" label="Email" :nilai="$pengguna->email" disabled
                                 bantuan="Email tidak bisa diubah. Hubungi admin bila perlu." />
                        <x-kolom nama="telepon" label="Nomor WhatsApp" wajib :nilai="$pengguna->telepon" placeholder="08xxxxxxxxxx" />
                    </div>

                    <x-pilihan nama="wilayah_id" label="Kelurahan" wajib :nilai="$pengguna->wilayah_id"
                               bantuan="Dipakai untuk menghitung jarak ke pengepul atau warga.">
                        @foreach ($kelurahan as $namaKecamatan => $daftar)
                            <optgroup label="Kec. {{ $namaKecamatan }}">
                                @foreach ($daftar as $w)
                                    <option value="{{ $w->id }}" @selected(old('wilayah_id', $pengguna->wilayah_id) == $w->id)>{{ $w->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-pilihan>

                    <x-kolom nama="alamat_detail" label="Alamat lengkap" tipe="textarea" rows="2" wajib :nilai="$pengguna->alamat_detail" />

                    <div class="flex justify-end">
                        <x-tombol type="submit" variant="primer" ikon="cek">Simpan perubahan</x-tombol>
                    </div>
                </form>
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Ganti kata sandi">
                <form method="POST" action="{{ route($awalan.'.sandi') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-kolom nama="current_password" label="Kata sandi saat ini" tipe="password" wajib autocomplete="current-password" />
                    <x-kolom nama="password" label="Kata sandi baru" tipe="password" wajib autocomplete="new-password" bantuan="Minimal 8 karakter." />
                    <x-kolom nama="password_confirmation" label="Ulangi kata sandi baru" tipe="password" wajib autocomplete="new-password" />
                    <x-tombol type="submit" variant="sekunder" penuh ikon="perisai">Ganti kata sandi</x-tombol>
                </form>
            </x-kartu>

            <x-kartu>
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Peran">{{ $pengguna->peran->label() }}</x-baris-info>
                    <x-baris-info label="Bergabung">{{ tanggal_id($pengguna->created_at) }}</x-baris-info>
                    <x-baris-info label="Lokasi">{{ $pengguna->wilayah?->namaLengkap() ?? '-' }}</x-baris-info>
                </dl>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
