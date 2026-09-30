@php
    use App\Enums\PeranPengguna;
@endphp
<x-layouts.panel judul="Pengguna" :keterangan="$pengguna->total().' akun'">
    @section('judul', 'Pengguna')

    @php
        $opsi = [['label' => 'Semua', 'href' => route('admin.pengguna.index'), 'aktif' => ! $peran, 'jumlah' => $jumlah->sum()]];
        foreach (PeranPengguna::cases() as $p) {
            $opsi[] = ['label' => $p->label(), 'href' => route('admin.pengguna.index', ['peran' => $p->value]), 'aktif' => $peran === $p, 'jumlah' => $jumlah[$p->value] ?? 0];
        }
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <x-pil-saring :opsi="$opsi" />
        <form method="GET" action="{{ route('admin.pengguna.index') }}" class="flex w-full gap-2 sm:w-auto">
            @if ($peran) <input type="hidden" name="peran" value="{{ $peran->value }}"> @endif
            <x-pilihan nama="status" :nilai="$status" kosong="Semua status" :opsi="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" class="w-36" />
            <x-kolom nama="q" :nilai="request('q')" placeholder="Nama, email, telepon" class="sm:w-56" />
            <x-tombol type="submit" variant="sekunder" ikon="cari"><span class="sr-only">Cari</span></x-tombol>
        </form>
    </div>

    <x-kartu>
        <div class="-mx-5 -my-5 overflow-x-auto">
            <table class="w-full min-w-[44rem] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">Nama</th>
                        <th class="py-3 pr-3 font-semibold">Peran</th>
                        <th class="py-3 pr-3 font-semibold">Wilayah</th>
                        <th class="py-3 pr-3 font-semibold">Status</th>
                        <th class="py-3 pr-5 text-right font-semibold">Bergabung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($pengguna as $u)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.pengguna.show', $u) }}" class="flex items-center gap-3">
                                    <x-avatar :nama="$u->name" :foto="$u->foto_profil" ukuran="size-9" />
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-900 hover:text-merk-700 dark:text-white">{{ $u->name }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $u->email }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="py-3 pr-3">
                                <x-lencana :warna="match ($u->peran) { PeranPengguna::Admin => 'violet', PeranPengguna::Pengepul => 'amber', default => 'sky' }">{{ $u->peran->label() }}</x-lencana>
                                @if ($u->profilPengepul) <p class="mt-1 truncate text-xs text-slate-500">{{ $u->profilPengepul->nama_usaha }}</p> @endif
                            </td>
                            <td class="py-3 pr-3 text-slate-600 dark:text-slate-400">{{ $u->wilayah?->nama ?? '-' }}</td>
                            <td class="py-3 pr-3"><x-lencana :warna="$u->aktif ? 'emerald' : 'rose'">{{ $u->aktif ? 'Aktif' : 'Nonaktif' }}</x-lencana></td>
                            <td class="py-3 pr-5 text-right text-xs text-slate-500">{{ tanggal_id($u->created_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-kosong ikon="pengguna-grup" judul="Tidak ada pengguna" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-6">{{ $pengguna->links() }}</div>
</x-layouts.panel>
