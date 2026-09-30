@php $baru = ! $kategori->exists; @endphp

<x-layouts.panel :judul="$baru ? 'Kategori Baru' : 'Ubah '.$kategori->nama" keterangan="Kategori sampah">
    @section('judul', $baru ? 'Kategori Baru' : 'Ubah Kategori')

    <x-slot:aksi>
        <x-tombol :href="route('admin.kategori.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Kembali</x-tombol>
    </x-slot:aksi>

    <form method="POST" action="{{ $baru ? route('admin.kategori.store') : route('admin.kategori.update', $kategori) }}" class="max-w-3xl">
        @csrf
        @unless ($baru) @method('PUT') @endunless

        <x-kartu>
            <div class="space-y-5">
                <x-pilihan nama="induk_id" label="Golongan induk" :opsi="$induk->all()" :nilai="$kategori->induk_id"
                           kosong="— Jadikan golongan induk —"
                           bantuan="Golongan induk (Kertas, Plastik, dst.) hanya pengelompok. Harga dipasang pada turunannya." />

                <div class="grid gap-5 sm:grid-cols-4">
                    <div class="sm:col-span-3"><x-kolom nama="nama" label="Nama" wajib :nilai="$kategori->nama" /></div>
                    <x-kolom nama="ikon" label="Ikon (emoji)" :nilai="$kategori->ikon" placeholder="📄" />
                </div>

                <x-kolom nama="deskripsi" label="Deskripsi" tipe="textarea" rows="2" :nilai="$kategori->deskripsi" />

                <div class="grid gap-5 sm:grid-cols-4">
                    <x-pilihan nama="satuan" label="Satuan" :opsi="['kg' => 'kg', 'pcs' => 'pcs']" :nilai="$kategori->satuan" :kosong="false" />
                    <x-kolom nama="harga_acuan_min" label="Acuan min." tipe="number" min="0" awalan="Rp" :nilai="$kategori->harga_acuan_min" />
                    <x-kolom nama="harga_acuan_max" label="Acuan maks." tipe="number" min="0" awalan="Rp" :nilai="$kategori->harga_acuan_max" />
                    <x-kolom nama="faktor_co2_per_kg" label="Faktor CO₂" tipe="number" step="0.001" min="0" wajib :nilai="$kategori->faktor_co2_per_kg ?? 0" akhiran="kg" />
                </div>

                <x-kolom nama="urutan" label="Urutan tampil" tipe="number" min="0" :nilai="$kategori->urutan ?? 0" class="max-w-[10rem]" />

                <div class="space-y-3 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="hidden" name="aktif" value="0">
                        <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $kategori->aktif)) class="size-4 rounded text-merk-600 focus:ring-merk-500">
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Aktif (tampil ke warga dan pengepul)</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="hidden" name="limbah_b3" value="0">
                        <input type="checkbox" name="limbah_b3" value="1" @checked(old('limbah_b3', $kategori->limbah_b3)) class="size-4 rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Limbah B3 — hanya boleh diterima pengepul berizin</span>
                    </label>
                </div>

                <x-kolom nama="peringatan_b3" label="Teks peringatan B3" tipe="textarea" rows="3" :nilai="$kategori->peringatan_b3"
                         bantuan="Wajib bila limbah B3. Ditampilkan ke warga saat memilih kategori ini." />
            </div>

            <x-slot:kaki>
                <div class="flex justify-end">
                    <x-tombol type="submit" variant="primer" ikon="cek">{{ $baru ? 'Tambah kategori' : 'Simpan perubahan' }}</x-tombol>
                </div>
            </x-slot:kaki>
        </x-kartu>
    </form>
</x-layouts.panel>
