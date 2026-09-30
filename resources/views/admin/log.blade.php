<x-layouts.panel judul="Log Audit" keterangan="Jejak semua tindakan penting admin dan keuangan">
    @section('judul', 'Log Audit')

    @php
        $opsi = [['label' => 'Semua', 'href' => route('admin.log'), 'aktif' => ! request('aksi')]];
        foreach ($jenisAksi as $a) {
            $opsi[] = ['label' => ucfirst(str_replace('_', ' ', $a)), 'href' => route('admin.log', ['aksi' => $a]), 'aktif' => request('aksi') === $a];
        }
    @endphp

    <x-pil-saring :opsi="$opsi" class="mb-6" />

    <x-kartu>
        <div class="-mx-5 -my-5 overflow-x-auto">
            <table class="w-full min-w-[48rem] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-semibold">Waktu</th>
                        <th class="py-3 pr-3 font-semibold">Oleh</th>
                        <th class="py-3 pr-3 font-semibold">Aksi</th>
                        <th class="py-3 pr-3 font-semibold">Subjek</th>
                        <th class="py-3 pr-5 font-semibold">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($log as $l)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ tanggal_id($l->created_at) }}<br>{{ $l->created_at->format('H:i:s') }}</td>
                            <td class="py-3 pr-3 text-slate-700 dark:text-slate-300">{{ $l->user?->name ?? 'Sistem' }}<br><span class="text-xs text-slate-400">{{ $l->alamat_ip }}</span></td>
                            <td class="py-3 pr-3"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $l->aksi }}</code></td>
                            <td class="py-3 pr-3 text-xs text-slate-500">{{ $l->subjek_type ? class_basename($l->subjek_type).' #'.$l->subjek_id : '-' }}</td>
                            <td class="max-w-md py-3 pr-5 text-xs">
                                @foreach (($l->nilai_baru ?? []) as $k => $v)
                                    <p class="truncate">
                                        <span class="text-slate-400">{{ $k }}:</span>
                                        @if (isset($l->nilai_lama[$k]))
                                            <span class="text-rose-600 line-through">{{ is_scalar($l->nilai_lama[$k]) ? $l->nilai_lama[$k] : json_encode($l->nilai_lama[$k]) }}</span> →
                                        @endif
                                        <span class="text-slate-700 dark:text-slate-300">{{ is_scalar($v) || $v === null ? var_export($v, true) : json_encode($v) }}</span>
                                    </p>
                                @endforeach
                                @if (empty($l->nilai_baru) && ! empty($l->nilai_lama))
                                    <p class="text-slate-500">{{ json_encode($l->nilai_lama, JSON_UNESCAPED_UNICODE) }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-kosong ikon="dokumen" judul="Belum ada catatan" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-6">{{ $log->links() }}</div>
</x-layouts.panel>
