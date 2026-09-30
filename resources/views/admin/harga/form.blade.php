@php $baru = ! $sumber->exists; @endphp

<x-layouts.panel :judul="$baru ? 'Harga Acuan Baru' : 'Ubah Harga Acuan'" keterangan="Setiap angka wajib bisa ditelusuri sumbernya">
    @section('judul', $baru ? 'Harga Acuan Baru' : 'Ubah Harga Acuan')

    <x-slot:aksi>
        <x-tombol :href="route('admin.harga.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Kembali</x-tombol>
    </x-slot:aksi>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $baru ? route('admin.harga.store') : route('admin.harga.update', $sumber) }}" class="max-w-3xl">
        @csrf
        @unless ($baru) @method('PUT') @endunless

        <x-kartu>
            <div class="space-y-5">
                <x-pilihan nama="kategori_sampah_id" label="Kategori" wajib kosong="Pilih kategori" :nilai="$sumber->kategori_sampah_id">
                    @foreach ($kategori as $namaGolongan => $daftar)
                        <optgroup label="{{ $namaGolongan }}">
                            @foreach ($daftar as $id => $nama)
                                <option value="{{ $id }}" @selected(old('kategori_sampah_id', $sumber->kategori_sampah_id) == $id)>{{ $nama }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-pilihan>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-kolom nama="harga_min" label="Harga minimum" tipe="number" min="0" awalan="Rp" wajib :nilai="$sumber->harga_min" />
                    <x-kolom nama="harga_max" label="Harga maksimum" tipe="number" min="0" awalan="Rp" wajib :nilai="$sumber->harga_max" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-kolom nama="sumber_nama" label="Nama sumber" wajib :nilai="$sumber->sumber_nama" placeholder="Contoh: Bank Sampah Induk Karawang" />
                    <x-pilihan nama="sumber_tipe" label="Jenis sumber" wajib :opsi="$tipe" :nilai="$sumber->sumber_tipe?->value" kosong="Pilih jenis" />
                </div>

                <x-kolom nama="sumber_url" label="Tautan sumber (opsional)" tipe="url" :nilai="$sumber->sumber_url" placeholder="https://..." />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-kolom nama="berlaku_mulai" label="Berlaku mulai" tipe="date" wajib :nilai="$sumber->berlaku_mulai?->toDateString()" />
                    <x-kolom nama="berlaku_sampai" label="Berlaku sampai (opsional)" tipe="date" :nilai="$sumber->berlaku_sampai?->toDateString()" />
                </div>

                <x-unggah nama="dokumen_bukti" label="Dokumen bukti (opsional)" accept="application/pdf,image/jpeg,image/png"
                          :sudah-ada="$sumber->dokumen_bukti ? 'Dokumen sudah ada; unggah lagi untuk mengganti' : null"
                          bantuan="Daftar harga resmi, foto papan harga, atau notulen survei. PDF/JPG/PNG, maks. 4 MB. Dapat diunduh publik." />
            </div>

            <x-slot:kaki>
                <div class="flex justify-end">
                    <x-tombol type="submit" variant="primer" ikon="cek">Simpan</x-tombol>
                </div>
            </x-slot:kaki>
        </x-kartu>
    </form>
</x-layouts.panel>
