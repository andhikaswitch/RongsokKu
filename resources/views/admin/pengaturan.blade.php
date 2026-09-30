<x-layouts.panel judul="Pengaturan Platform" keterangan="Perubahan langsung berlaku dan tercatat di log audit">
    @section('judul', 'Pengaturan Platform')

    @php
        $namaGrup = ['keuangan' => 'Keuangan & Komisi', 'harga' => 'Harga & Indeks', 'umum' => 'Umum'];
    @endphp

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        @foreach ($grup as $kunciGrup => $daftar)
            <x-kartu :judul="$namaGrup[$kunciGrup] ?? ucfirst($kunciGrup)">
                <div class="space-y-5">
                    @foreach ($daftar as $p)
                        <x-kolom :nama="'pengaturan['.$p->kunci.']'" :id="'p-'.$p->kunci"
                                 :label="$p->label" :tipe="$p->tipe === 'angka' ? 'number' : 'text'" step="any"
                                 :nilai="old('pengaturan.'.$p->kunci, $p->nilai)" :bantuan="$p->keterangan" />
                        @error('pengaturan.'.$p->kunci)
                            <p class="-mt-3 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>
            </x-kartu>
        @endforeach

        <div class="rounded-2xl bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-100">
            Mengubah tarif komisi hanya berlaku untuk transaksi yang selesai <strong>setelah</strong> perubahan.
            Transaksi lama menyimpan tarifnya sendiri sehingga riwayat tetap akurat.
        </div>

        <div class="flex justify-end">
            <x-tombol type="submit" variant="primer" ikon="cek">Simpan pengaturan</x-tombol>
        </div>
    </form>
</x-layouts.panel>
