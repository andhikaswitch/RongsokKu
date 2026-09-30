{{--
    Tabel barang: taksiran warga berdampingan dengan hasil timbangan.
    Butuh: $permintaan. Selisih ditampilkan agar kedua pihak bisa menilai.
--}}
@php
    $p = $permintaan;
    $sudahDitimbang = $p->item->contains(fn ($i) => $i->berat_final !== null);
@endphp

<div class="-mx-5 overflow-x-auto">
    <table class="w-full min-w-[34rem] text-sm">
        <thead>
            <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400 dark:border-slate-800">
                <th class="px-5 pb-2 font-semibold">Barang</th>
                <th class="pb-2 pr-4 text-right font-semibold">Taksiran</th>
                <th class="pb-2 pr-4 text-right font-semibold">Harga disepakati</th>
                @if ($sudahDitimbang)
                    <th class="pb-2 pr-4 text-right font-semibold">Timbangan</th>
                    <th class="pb-2 pr-5 text-right font-semibold">Dibayar</th>
                @else
                    <th class="pb-2 pr-5 text-right font-semibold">Estimasi</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($p->item as $item)
                @php
                    $selisihBerat = $item->selisihBeratPersen();
                    $hargaTurun = $item->harga_final_per_satuan !== null
                        && (float) $item->harga_final_per_satuan < (float) $item->harga_estimasi_per_satuan;
                @endphp
                <tr>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-lg dark:bg-slate-800">
                                {{ $item->kategori->ikon }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-white">
                                    {{ $item->kategori->nama }}
                                    @if ($item->kategori->limbah_b3) <span title="Limbah B3">⚠️</span> @endif
                                </p>
                                @if ($item->catatan)
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $item->catatan }}</p>
                                @endif
                                @if ($item->foto_barang)
                                    <a href="{{ route('berkas.barang', $item) }}" target="_blank"
                                       class="text-xs font-semibold text-merk-700 hover:underline dark:text-merk-400">Lihat foto</a>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 pr-4 text-right tabular-nums text-slate-600 dark:text-slate-400">
                        {{ berat($item->estimasi_berat, $item->kategori->satuan) }}
                    </td>
                    <td class="py-3 pr-4 text-right tabular-nums text-slate-600 dark:text-slate-400">
                        {{ rupiah($item->harga_estimasi_per_satuan) }}
                    </td>

                    @if ($sudahDitimbang)
                        <td class="py-3 pr-4 text-right tabular-nums">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ berat($item->berat_final, $item->kategori->satuan) }}</span>
                            @if ($selisihBerat !== null && abs($selisihBerat) >= 0.5)
                                <span @class([
                                    'block text-xs',
                                    'text-emerald-600 dark:text-emerald-400' => $selisihBerat > 0,
                                    'text-amber-600 dark:text-amber-400' => $selisihBerat < 0,
                                ])>{{ \App\Support\Format::persen($selisihBerat) }} dari taksiran</span>
                            @endif
                        </td>
                        <td class="py-3 pr-5 text-right tabular-nums">
                            <span class="font-bold text-slate-900 dark:text-white">{{ rupiah($item->subtotal_final) }}</span>
                            <span @class([
                                'block text-xs',
                                'font-semibold text-rose-600 dark:text-rose-400' => $hargaTurun,
                                'text-slate-400' => ! $hargaTurun,
                            ])>
                                @ {{ rupiah($item->harga_final_per_satuan) }}
                                @if ($hargaTurun) · di bawah kesepakatan @endif
                            </span>
                        </td>
                    @else
                        <td class="py-3 pr-5 text-right font-bold tabular-nums text-slate-900 dark:text-white">
                            {{ rupiah($item->subtotal_estimasi) }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                <td class="px-5 pt-3 font-bold text-slate-900 dark:text-white">Total</td>
                <td class="pt-3 pr-4 text-right font-semibold tabular-nums text-slate-600 dark:text-slate-400">
                    {{ berat($p->estimasi_berat_kg) }}
                </td>
                <td></td>
                @if ($sudahDitimbang)
                    <td class="pt-3 pr-4 text-right font-bold tabular-nums text-slate-900 dark:text-white">
                        {{ berat($p->berat_final_kg) }}
                    </td>
                    <td class="pt-3 pr-5 text-right text-lg font-extrabold tabular-nums text-merk-700 dark:text-merk-400">
                        {{ rupiah($p->total_final) }}
                    </td>
                @else
                    <td class="pt-3 pr-5 text-right text-lg font-extrabold tabular-nums text-slate-900 dark:text-white">
                        {{ rupiah($p->estimasi_total) }}
                    </td>
                @endif
            </tr>
        </tfoot>
    </table>
</div>

@if ($p->permintaan_terbuka && ! $p->profil_pengepul_id)
    <p class="mt-4 rounded-xl bg-violet-50 p-3 text-xs leading-relaxed text-violet-800 dark:bg-violet-500/10 dark:text-violet-300">
        Ini permintaan terbuka. Harga di atas masih perkiraan dari indeks pasar; harga pengepul yang
        mengklaim akan dikunci begitu permintaan diambil.
    </p>
@endif
