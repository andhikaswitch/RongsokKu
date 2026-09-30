<x-layouts.panel :judul="'Sengketa '.$sengketa->kode" :keterangan="$sengketa->alasan">
    @section('judul', 'Sengketa '.$sengketa->kode)

    @php
        $p = $sengketa->permintaan;
        $seharusnya = $p->item->sum(fn ($i) => (float) $i->berat_final * (float) $i->harga_estimasi_per_satuan);
        $selisih = $p->selisihHargaPersen();
    @endphp

    <x-slot:aksi>
        <x-tombol :href="route('admin.sengketa.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Semua</x-tombol>
    </x-slot:aksi>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-kartu judul="Keberatan warga">
                <p class="font-semibold text-slate-900 dark:text-white">{{ $sengketa->alasan }}</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $sengketa->deskripsi }}</p>
                @if ($sengketa->bukti)
                    <a href="{{ route('berkas.sengketa', $sengketa) }}" target="_blank" class="mt-4 block">
                        <img src="{{ route('berkas.sengketa', $sengketa) }}" alt="Bukti sengketa" class="max-h-72 rounded-xl bg-slate-100 object-contain dark:bg-slate-800">
                    </a>
                @endif
                @if ($p->catatan_pengepul)
                    <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan pengepul saat menimbang</p>
                        <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $p->catatan_pengepul }}</p>
                    </div>
                @endif
            </x-kartu>

            <x-kartu judul="Bukti angka" keterangan="Harga dikunci saat pengajuan vs yang dibayar di lokasi">
                @include('partials.permintaan.rincian-barang', ['permintaan' => $p])

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                        <p class="text-xs text-slate-500">Seharusnya (harga kesepakatan)</p>
                        <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900 dark:text-white">{{ rupiah($seharusnya) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                        <p class="text-xs text-slate-500">Dibayar pengepul</p>
                        <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900 dark:text-white">{{ rupiah($p->total_final) }}</p>
                    </div>
                    <div class="rounded-xl bg-rose-50 p-4 dark:bg-rose-500/10">
                        <p class="text-xs text-rose-700 dark:text-rose-300">Selisih</p>
                        <p class="mt-1 text-lg font-extrabold tabular-nums text-rose-700 dark:text-rose-300">
                            {{ rupiah((float) $p->total_final - $seharusnya) }}
                            @if ($selisih !== null) <span class="text-sm">({{ \App\Support\Format::persen($selisih) }})</span> @endif
                        </p>
                    </div>
                </div>
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Pihak">
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Pelapor">{{ $sengketa->pelapor->name }}<br><span class="text-xs text-slate-500">{{ $sengketa->pelapor->telepon }}</span></x-baris-info>
                    <x-baris-info label="Pengepul">{{ $p->pengepul?->nama_usaha }}<br><span class="text-xs text-slate-500">{{ $p->pengepul?->user->telepon }}</span></x-baris-info>
                    <x-baris-info label="Kepatuhan harga">{{ $p->pengepul ? rtrim(rtrim(number_format((float) $p->pengepul->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',').'%' : '-' }}</x-baris-info>
                    <x-baris-info label="Transaksi">
                        <a href="{{ route('admin.transaksi.show', $p) }}" class="text-merk-700 hover:underline dark:text-merk-400">{{ $p->kode }}</a>
                    </x-baris-info>
                </dl>
            </x-kartu>

            @if ($sengketa->status === 'menunggu')
                <x-kartu judul="Putuskan">
                    <form method="POST" action="{{ route('admin.sengketa.putuskan', $sengketa) }}" class="space-y-4">
                        @csrf
                        <fieldset class="space-y-2">
                            @foreach ($keputusan as $nilai => $label)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-slate-200 has-[:checked]:bg-merk-50 has-[:checked]:ring-2 has-[:checked]:ring-merk-600 dark:ring-slate-700 dark:has-[:checked]:bg-merk-500/10">
                                    <input type="radio" name="keputusan" value="{{ $nilai }}" class="mt-0.5 text-merk-600 focus:ring-merk-500" @checked(old('keputusan', 'harga_awal') === $nilai)>
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $label }}</span>
                                </label>
                            @endforeach
                        </fieldset>

                        <x-kolom nama="resolusi" label="Catatan keputusan" tipe="textarea" rows="3" wajib
                                 placeholder="Tampil ke warga dan pengepul. Jelaskan dasar keputusan." />

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-rose-50 p-3 dark:bg-rose-500/10">
                            <input type="checkbox" name="sanksi" value="1" class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                            <span class="text-sm text-rose-800 dark:text-rose-200">
                                <strong>Nonaktifkan akun pengepul</strong> sebagai sanksi (misalnya terbukti memasang harga umpan berulang kali).
                            </span>
                        </label>

                        <x-tombol type="submit" variant="primer" penuh ikon="cek">Simpan keputusan</x-tombol>
                    </form>
                </x-kartu>
            @else
                <x-kartu judul="Keputusan">
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $sengketa->resolusi }}</p>
                    <p class="mt-3 text-xs text-slate-400">Oleh {{ $sengketa->penangan?->name }}, {{ tanggal_id($sengketa->ditangani_pada) }}</p>
                </x-kartu>
            @endif
        </div>
    </div>
</x-layouts.panel>
