@php
    use App\Enums\StatusVerifikasi;
@endphp
<x-layouts.panel :judul="$pengepul->nama_usaha" keterangan="Pemeriksaan berkas verifikasi">
    @section('judul', 'Verifikasi '.$pengepul->nama_usaha)

    @php $u = $pengepul->user; @endphp

    <x-slot:aksi>
        <x-tombol :href="route('admin.verifikasi.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Antrean</x-tombol>
    </x-slot:aksi>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-kartu judul="Foto KTP" keterangan="Data pribadi — jangan disebarkan" padat>
                    @if ($pengepul->foto_ktp)
                        <a href="{{ route('berkas.ktp', $pengepul) }}" target="_blank">
                            <img src="{{ route('berkas.ktp', $pengepul) }}" alt="KTP {{ $u->name }}" class="h-56 w-full rounded-xl bg-slate-100 object-contain dark:bg-slate-800">
                        </a>
                    @else
                        <x-kosong ikon="dokumen" judul="Belum diunggah" />
                    @endif
                </x-kartu>
                <x-kartu judul="Foto lapak" padat>
                    @if ($pengepul->foto_lapak)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($pengepul->foto_lapak) }}" target="_blank">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($pengepul->foto_lapak) }}" alt="Lapak {{ $pengepul->nama_usaha }}" class="h-56 w-full rounded-xl bg-slate-100 object-cover dark:bg-slate-800">
                        </a>
                    @else
                        <x-kosong ikon="toko" judul="Belum diunggah" />
                    @endif
                </x-kartu>
            </div>

            <x-kartu judul="Data lapak">
                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Nama usaha">{{ $pengepul->nama_usaha }}</x-baris-info>
                    <x-baris-info label="Pemilik">{{ $u->name }}</x-baris-info>
                    <x-baris-info label="Email">{{ $u->email }}</x-baris-info>
                    <x-baris-info label="WhatsApp">{{ $u->telepon }}</x-baris-info>
                    <x-baris-info label="Alamat">{{ $u->alamat_detail }}, {{ $u->wilayah?->namaLengkap() }}</x-baris-info>
                    <x-baris-info label="Mengaku berizin B3">{{ $pengepul->izin_b3 ? 'Ya — minta bukti izin bila ragu' : 'Tidak' }}</x-baris-info>
                    <x-baris-info label="Terdaftar">{{ tanggal_id($u->created_at) }}</x-baris-info>
                </dl>
                @if ($pengepul->deskripsi)
                    <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-800/50 dark:text-slate-400">{{ $pengepul->deskripsi }}</p>
                @endif
            </x-kartu>

            <x-kartu judul="Lokasi" padat>
                <x-peta :lat="$u->latitude" :lng="$u->longitude" tinggi="h-56" />
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Keputusan">
                <x-lencana-status :status="$pengepul->status_verifikasi" />

                @if ($pengepul->status_verifikasi === StatusVerifikasi::Menunggu)
                    <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li>☐ Nama di KTP sesuai nama pemilik</li>
                        <li>☐ Foto lapak menunjukkan usaha rongsok nyata</li>
                        <li>☐ Alamat masuk akal dengan kelurahan</li>
                        <li>☐ Nomor WhatsApp aktif</li>
                    </ul>
                    <div class="mt-5 space-y-2">
                        <form method="POST" action="{{ route('admin.verifikasi.setujui', $pengepul) }}">
                            @csrf
                            <x-tombol type="submit" variant="primer" penuh ikon="cek">Setujui verifikasi</x-tombol>
                        </form>
                        <x-tombol href="#modal-tolak" variant="bahaya-halus" penuh ikon="silang">Tolak</x-tombol>
                    </div>
                @elseif ($pengepul->status_verifikasi === StatusVerifikasi::Ditolak)
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Alasan: {{ $pengepul->alasan_penolakan }}</p>
                @elseif ($pengepul->diverifikasi_pada)
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Diverifikasi {{ tanggal_id($pengepul->diverifikasi_pada) }}.</p>
                @endif
            </x-kartu>

            <x-tombol :href="route('admin.pengguna.show', $u)" variant="sekunder" penuh ikon="pengguna">Lihat akun pengguna</x-tombol>
        </div>
    </div>

    <x-modal id="modal-tolak" judul="Tolak verifikasi" keterangan="Pengepul akan melihat alasan ini dan bisa mengajukan ulang.">
        <form method="POST" action="{{ route('admin.verifikasi.tolak', $pengepul) }}" class="space-y-4">
            @csrf
            <x-kolom nama="alasan" label="Alasan penolakan" wajib placeholder="Contoh: foto KTP buram, mohon unggah ulang" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                <x-tombol type="submit" variant="bahaya">Tolak</x-tombol>
            </div>
        </form>
    </x-modal>
</x-layouts.panel>
