<x-layouts.panel judul="Daftar Harga" keterangan="Harga beli Anda per kategori, dibandingkan indeks pasar">
    @section('judul', 'Daftar Harga')

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl bg-sky-50 p-5 text-sm leading-relaxed text-sky-900 ring-1 ring-inset ring-sky-600/20 lg:col-span-2 dark:bg-sky-500/10 dark:text-sky-100 dark:ring-sky-400/30">
            <p class="font-bold">Anda bebas menentukan harga.</p>
            <p class="mt-1">
                Sistem tidak menolak harga apa pun. Tapi warga melihat seberapa jauh harga Anda dari
                Indeks RongsokKu, dan harga yang Anda pasang <strong>dikunci</strong> saat warga mengajukan.
                Membayar di bawah harga itu akan menurunkan skor kepatuhan harga Anda.
            </p>
        </div>
        <x-statistik label="Kategori dipasang" :nilai="$hargaSaya->count()" ikon="grafik" :keterangan="$hargaSaya->where('sedang_menerima', true)->count().' sedang menerima'" />
    </div>

    <form method="POST" action="{{ route('pengepul.harga.simpan') }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            @foreach ($golongan as $g)
                <x-kartu :judul="$g->ikon.' '.$g->nama" :keterangan="$g->deskripsi">
                    <div class="-mx-5 overflow-x-auto">
                        <table class="w-full min-w-[46rem] text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400 dark:border-slate-800">
                                    <th class="px-5 pb-2 font-semibold">Kategori</th>
                                    <th class="pb-2 pr-3 font-semibold">Indeks · Acuan</th>
                                    <th class="w-40 pb-2 pr-3 font-semibold">Harga Anda</th>
                                    <th class="w-28 pb-2 pr-3 font-semibold">Min. berat</th>
                                    <th class="pb-2 pr-5 text-center font-semibold">Menerima</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($g->anak as $k)
                                    @php
                                        $h = $hargaSaya[$k->id] ?? null;
                                        $median = $indeks[$k->id] ?? null;
                                        $nilaiHarga = old("harga.{$k->id}.harga", $h ? (float) $h->harga_per_satuan : '');
                                        $selisih = $median && $nilaiHarga !== '' && (float) $nilaiHarga > 0
                                            ? round(((float) $nilaiHarga - (float) $median) / (float) $median * 100, 1) : null;
                                        $terkunci = $k->limbah_b3 && ! $profil->izin_b3;
                                    @endphp
                                    <tr @class(['opacity-50' => $terkunci])>
                                        <td class="px-5 py-3">
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ $k->nama }}
                                                @if ($k->limbah_b3) <x-lencana warna="rose" class="ml-1">B3</x-lencana> @endif
                                            </p>
                                            <p class="text-xs text-slate-400">per {{ $k->satuan }}</p>
                                        </td>
                                        <td class="py-3 pr-3 text-xs">
                                            <p class="font-semibold text-slate-700 tabular-nums dark:text-slate-300">
                                                {{ $median ? rupiah($median) : 'Indeks belum cukup' }}
                                            </p>
                                            @if ($k->harga_acuan_min)
                                                <p class="text-slate-400 tabular-nums">{{ rupiah($k->harga_acuan_min) }}–{{ rupiah($k->harga_acuan_max) }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-3">
                                            @if ($terkunci)
                                                <p class="text-xs text-slate-500">Butuh izin B3</p>
                                            @else
                                                <x-kolom :nama="'harga['.$k->id.'][harga]'" tipe="number" min="0" step="25"
                                                         awalan="Rp" :nilai="$nilaiHarga" placeholder="Tidak beli"
                                                         :aria-label="'Harga '.$k->nama" />
                                                @if ($selisih !== null && abs($selisih) >= $ambang)
                                                    <p @class([
                                                        'mt-1 text-xs font-semibold',
                                                        'text-rose-600 dark:text-rose-400' => $selisih < 0,
                                                        'text-amber-600 dark:text-amber-400' => $selisih > 0,
                                                    ])>
                                                        {{ \App\Support\Format::persen($selisih) }} dari indeks
                                                    </p>
                                                @elseif ($selisih !== null)
                                                    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Wajar ({{ \App\Support\Format::persen($selisih) }})</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 pr-3">
                                            @unless ($terkunci)
                                                <x-kolom :nama="'harga['.$k->id.'][min_berat]'" tipe="number" min="0" step="0.5"
                                                         akhiran="kg" :nilai="old('harga.'.$k->id.'.min_berat', $h ? (float) $h->min_berat : 0)"
                                                         :aria-label="'Berat minimum '.$k->nama" />
                                            @endunless
                                        </td>
                                        <td class="py-3 pr-5 text-center">
                                            @unless ($terkunci)
                                                <input type="checkbox" name="harga[{{ $k->id }}][menerima]" value="1"
                                                       @checked(old('harga.'.$k->id.'.menerima', $h ? $h->sedang_menerima : true))
                                                       class="size-5 rounded border-slate-300 text-merk-600 focus:ring-merk-500"
                                                       aria-label="Sedang menerima {{ $k->nama }}">
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-kartu>
            @endforeach
        </div>

        <div class="sticky bottom-20 z-20 mt-6 flex items-center justify-between gap-4 rounded-2xl bg-white/95 p-4 shadow-[var(--shadow-naik)] ring-1 ring-slate-200 backdrop-blur lg:bottom-4 dark:bg-slate-900/95 dark:ring-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Kosongkan harga untuk kategori yang tidak Anda beli. Persentase di atas diperbarui setelah disimpan.
            </p>
            <x-tombol type="submit" variant="primer" ikon="cek">Simpan harga</x-tombol>
        </div>
    </form>
</x-layouts.panel>
