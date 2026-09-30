<x-layouts.panel judul="Isi Saldo" keterangan="Transfer, lalu unggah bukti">
    @section('judul', 'Isi Saldo')

    <x-slot:aksi>
        <x-tombol :href="route('pengepul.dompet.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Dompet</x-tombol>
    </x-slot:aksi>

    <div class="grid max-w-5xl gap-6 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <div class="rounded-2xl bg-gradient-to-br from-slate-800 to-slate-950 p-6 text-white">
                <p class="text-sm text-white/70">Transfer ke</p>
                <p class="mt-3 text-lg font-bold">{{ pengaturan('bank_nama', 'Bank BCA') }}</p>
                <p class="mt-1 font-mono text-2xl font-extrabold tracking-wider">{{ pengaturan('bank_rekening', '-') }}</p>
                <p class="mt-1 text-sm text-white/80">a.n. {{ pengaturan('bank_atas_nama', 'RongsokKu') }}</p>
                <div class="mt-6 border-t border-white/15 pt-4 text-xs leading-relaxed text-white/70">
                    Minimal {{ rupiah(pengaturan('topup_minimum', 25000)) }}. Saldo bertambah setelah admin
                    mencocokkan bukti transfer, biasanya di hari kerja yang sama.
                </div>
            </div>

            <div class="mt-4 rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">Saldo saat ini</p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ rupiah($profil->saldo) }}</p>
            </div>
        </div>

        <div class="lg:col-span-3">
            <x-kartu judul="Data transfer">
                <form method="POST" action="{{ route('pengepul.dompet.topup.kirim') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Nominal <span class="text-rose-500">*</span></legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ([50000, 100000, 200000, 500000] as $n)
                                <label class="cursor-pointer">
                                    <input type="radio" name="jumlah" value="{{ $n }}" class="peer sr-only" @checked((int) old('jumlah', 100000) === $n)>
                                    <span class="block rounded-xl bg-white py-3 text-center text-sm font-bold tabular-nums text-slate-700 ring-1 ring-slate-200 transition
                                                 peer-checked:bg-merk-50 peer-checked:text-merk-700 peer-checked:ring-2 peer-checked:ring-merk-600
                                                 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-merk-600
                                                 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 dark:peer-checked:bg-merk-500/10 dark:peer-checked:text-merk-300">
                                        {{ rupiah($n, true) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('jumlah') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-pilihan nama="bank_pengirim" label="Bank pengirim" wajib kosong="Pilih bank" :opsi="collect(['Bank BCA', 'Bank BRI', 'Bank Mandiri', 'Bank BNI', 'Bank BSI', 'Bank Jago', 'SeaBank', 'Lainnya'])->mapWithKeys(fn ($b) => [$b => $b])->all()" />
                        <x-kolom nama="nama_pengirim" label="Nama pemilik rekening" wajib :nilai="auth()->user()->name" />
                    </div>

                    <x-unggah nama="bukti_transfer" label="Bukti transfer" wajib accept="image/jpeg,image/png,image/webp,application/pdf"
                              bantuan="Foto atau tangkapan layar struk transfer. JPG, PNG, atau PDF, maks. 2 MB." />

                    <div class="flex justify-end">
                        <x-tombol type="submit" variant="primer" ikon="unggah">Kirim pengajuan</x-tombol>
                    </div>
                </form>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
