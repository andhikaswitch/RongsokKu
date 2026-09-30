<x-layouts.panel :judul="$pengguna->name" :keterangan="$pengguna->peran->label()">
    @section('judul', $pengguna->name)

    @php $profil = $pengguna->profilPengepul; $diriSendiri = $pengguna->id === auth()->id(); @endphp

    <x-slot:aksi>
        <x-tombol :href="route('admin.pengguna.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Pengguna</x-tombol>
    </x-slot:aksi>

    @unless ($pengguna->aktif)
        <div class="mb-6 rounded-2xl bg-rose-50 p-5 text-sm text-rose-800 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-400/30">
            <p class="font-bold">Akun dinonaktifkan {{ $pengguna->disuspend_pada ? tanggal_id($pengguna->disuspend_pada) : '' }}</p>
            <p class="mt-1">Alasan: {{ $pengguna->alasan_suspend ?? '-' }}</p>
        </div>
    @endunless

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-kartu>
                <div class="flex flex-wrap items-center gap-4">
                    <x-avatar :nama="$pengguna->name" :foto="$pengguna->foto_profil" ukuran="size-16" />
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $pengguna->name }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $pengguna->email }} · {{ $pengguna->telepon }}</p>
                    </div>
                    <x-lencana :warna="$pengguna->aktif ? 'emerald' : 'rose'">{{ $pengguna->aktif ? 'Aktif' : 'Nonaktif' }}</x-lencana>
                </div>
                <dl class="mt-5 divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <x-baris-info label="Alamat">{{ $pengguna->alamat_detail ?? '-' }}</x-baris-info>
                    <x-baris-info label="Wilayah">{{ $pengguna->wilayah?->namaLengkap() ?? '-' }}</x-baris-info>
                    <x-baris-info label="Bergabung">{{ tanggal_id($pengguna->created_at) }}</x-baris-info>
                    @if ($pengguna->isWarga())
                        <x-baris-info label="Total didaur ulang">{{ berat($pengguna->totalBeratDidaurUlang()) }}</x-baris-info>
                        <x-baris-info label="Lencana">{{ $pengguna->lencana->pluck('ikon')->join(' ') ?: '-' }}</x-baris-info>
                    @endif
                </dl>
            </x-kartu>

            @if ($profil)
                <x-kartu judul="Profil pengepul" :keterangan="$profil->nama_usaha">
                    <div class="grid gap-4 sm:grid-cols-4">
                        <x-statistik label="Saldo" :nilai="rupiah($profil->saldo)" />
                        <x-statistik label="Transaksi" :nilai="$profil->total_transaksi" />
                        <x-statistik label="Rating" :nilai="number_format((float) $profil->rating_rata, 1, ',', '.')" />
                        <x-statistik label="Kepatuhan harga" :nilai="rtrim(rtrim(number_format((float) $profil->skor_kepatuhan_harga, 1, ',', '.'), '0'), ',').'%'" />
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-lencana-status :status="$profil->status_verifikasi" />
                        <x-tombol :href="route('admin.verifikasi.show', $profil)" variant="hantu" ukuran="kecil">Berkas verifikasi</x-tombol>
                        @if ($profil->terverifikasi())
                            <x-tombol :href="route('pengepul.detail', $profil)" target="_blank" variant="hantu" ukuran="kecil">Halaman publik</x-tombol>
                        @endif
                    </div>
                </x-kartu>
            @endif

            <x-kartu judul="Permintaan terakhir">
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($permintaan as $p)
                        <a href="{{ route('admin.transaksi.show', $p) }}" class="flex items-center justify-between gap-3 py-2.5 hover:opacity-75">
                            <span class="min-w-0">
                                <span class="font-mono text-xs font-semibold text-slate-500">{{ $p->kode }}</span>
                                <span class="block truncate text-sm text-slate-700 dark:text-slate-300">
                                    {{ $pengguna->isPengepul() ? $p->warga?->name : ($p->pengepul?->nama_usaha ?? '— terbuka —') }}
                                </span>
                            </span>
                            <x-lencana-status :status="$p->status" />
                        </a>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada permintaan.</p>
                    @endforelse
                </div>
            </x-kartu>
        </div>

        <x-kartu judul="Tindakan" class="h-fit">
            @if ($diriSendiri)
                <p class="text-sm text-slate-500 dark:text-slate-400">Ini akun Anda sendiri.</p>
            @else
                <div class="space-y-2">
                    @if ($pengguna->aktif)
                        <x-tombol href="#modal-nonaktif" variant="bahaya-halus" penuh ikon="silang">Nonaktifkan akun</x-tombol>
                    @else
                        <form method="POST" action="{{ route('admin.pengguna.aktifkan', $pengguna) }}">
                            @csrf
                            <x-tombol type="submit" variant="primer" penuh ikon="cek">Aktifkan kembali</x-tombol>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.pengguna.sandi', $pengguna) }}">
                        @csrf
                        <x-tombol type="submit" variant="sekunder" penuh ikon="perisai">Atur ulang kata sandi</x-tombol>
                    </form>
                </div>
                <p class="mt-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Semua tindakan tercatat di log audit beserta nama admin yang melakukannya.
                </p>
            @endif
        </x-kartu>
    </div>

    <x-modal id="modal-nonaktif" judul="Nonaktifkan akun?" keterangan="Pengguna tidak akan bisa masuk sampai diaktifkan kembali.">
        <form method="POST" action="{{ route('admin.pengguna.nonaktifkan', $pengguna) }}" class="space-y-4">
            @csrf
            <x-kolom nama="alasan" label="Alasan" wajib placeholder="Contoh: terbukti memasang harga umpan berulang kali" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                <x-tombol type="submit" variant="bahaya">Nonaktifkan</x-tombol>
            </div>
        </form>
    </x-modal>
</x-layouts.panel>
