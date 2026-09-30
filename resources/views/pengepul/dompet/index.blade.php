@php
    use App\Enums\JenisMutasi;
@endphp
<x-layouts.panel judul="Dompet & Saldo" keterangan="Saldo dipakai untuk membayar komisi platform">
    @section('judul', 'Dompet & Saldo')

    @php
        $minimum = (float) pengaturan('saldo_minimum', 10000);
        $cukup = $profil->saldoCukup();
        $opsi = [['label' => 'Semua', 'href' => route('pengepul.dompet.index'), 'aktif' => ! $jenis]];
        foreach (JenisMutasi::cases() as $j) {
            $opsi[] = ['label' => $j->label(), 'href' => route('pengepul.dompet.index', ['jenis' => $j->value]), 'aktif' => $jenis === $j];
        }
    @endphp

    <x-slot:aksi>
        <x-tombol :href="route('pengepul.dompet.topup')" variant="primer" ukuran="kecil" ikon="tambah">Isi Saldo</x-tombol>
    </x-slot:aksi>

    <div class="grid gap-4 lg:grid-cols-3">
        <div @class([
            'relative overflow-hidden rounded-2xl p-6 text-white lg:col-span-1',
            'bg-gradient-to-br from-merk-600 to-merk-800' => $cukup,
            'bg-gradient-to-br from-rose-600 to-rose-800' => ! $cukup,
        ])>
            <div class="pola-titik pointer-events-none absolute inset-0 text-white/10"></div>
            <p class="relative text-sm font-medium text-white/80">Saldo tersedia</p>
            <p class="relative mt-2 text-3xl font-extrabold tracking-tight tabular-nums">{{ rupiah($profil->saldo) }}</p>
            <p class="relative mt-2 text-xs text-white/80">
                @if ($cukup)
                    Minimum {{ rupiah($minimum) }} untuk menerima permintaan.
                @else
                    Di bawah minimum {{ rupiah($minimum) }}. Isi saldo untuk menerima permintaan baru.
                @endif
            </p>
            <x-tombol :href="route('pengepul.dompet.topup')" variant="sekunder" ukuran="kecil" class="relative mt-5" ikon="tambah">
                Isi saldo
            </x-tombol>
        </div>

        <x-statistik label="Total pernah diisi" :nilai="rupiah($ringkasan['masuk'])" ikon="dompet" warna="sky" />
        <x-statistik label="Total komisi dibayar" :nilai="rupiah($ringkasan['komisi'])" ikon="petir" warna="violet"
                     :keterangan="pengaturan('tarif_komisi', 5).'% dari tiap transaksi selesai'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-kartu judul="Riwayat mutasi" keterangan="Buku besar saldo Anda">
                <x-pil-saring :opsi="$opsi" class="mb-4" />

                <div class="-mx-5 overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400 dark:border-slate-800">
                                <th class="px-5 pb-2 font-semibold">Waktu</th>
                                <th class="pb-2 pr-3 font-semibold">Keterangan</th>
                                <th class="pb-2 pr-3 text-right font-semibold">Jumlah</th>
                                <th class="pb-2 pr-5 text-right font-semibold">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($mutasi as $m)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ tanggal_id($m->created_at) }}<br>{{ $m->created_at->format('H:i') }}
                                    </td>
                                    <td class="py-3 pr-3">
                                        <p class="font-medium text-slate-900 dark:text-white">{{ $m->jenis->label() }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $m->keterangan }}</p>
                                    </td>
                                    <td @class([
                                        'py-3 pr-3 text-right font-bold tabular-nums',
                                        'text-emerald-600 dark:text-emerald-400' => (float) $m->jumlah > 0,
                                        'text-rose-600 dark:text-rose-400' => (float) $m->jumlah < 0,
                                    ])>
                                        {{ (float) $m->jumlah > 0 ? '+' : '−' }}{{ rupiah(abs((float) $m->jumlah)) }}
                                    </td>
                                    <td class="py-3 pr-5 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ rupiah($m->saldo_sesudah) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><x-kosong ikon="dompet" judul="Belum ada mutasi" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $mutasi->links() }}</div>
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Pengajuan isi saldo">
                <div class="space-y-3">
                    @forelse ($topup as $t)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                            <div class="min-w-0">
                                <p class="font-bold tabular-nums text-slate-900 dark:text-white">{{ rupiah($t->jumlah) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $t->kode }} · {{ tanggal_id($t->created_at) }}</p>
                                @if ($t->catatan_admin)
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $t->catatan_admin }}</p>
                                @endif
                            </div>
                            <x-lencana-status :status="$t->status" />
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum pernah mengisi saldo.</p>
                    @endforelse
                </div>
            </x-kartu>

            <x-kartu judul="Cara kerja saldo">
                <ol class="list-inside list-decimal space-y-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    <li>Transfer ke rekening RongsokKu, lalu unggah bukti transfer.</li>
                    <li>Admin memverifikasi, saldo bertambah.</li>
                    <li>Setiap transaksi selesai, komisi {{ pengaturan('tarif_komisi', 5) }}% dipotong otomatis.</li>
                    <li>Uang pembelian rongsok tetap Anda bayar tunai langsung ke warga.</li>
                </ol>
            </x-kartu>
        </div>
    </div>
</x-layouts.panel>
