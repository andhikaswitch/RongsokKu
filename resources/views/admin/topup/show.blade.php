@php
    use App\Enums\StatusTopup;
@endphp
<x-layouts.panel :judul="'Top-up '.$topup->kode" :keterangan="$topup->pengepul->nama_usaha">
    @section('judul', 'Top-up '.$topup->kode)

    @php
        $pdf = str_ends_with(strtolower((string) $topup->bukti_transfer), '.pdf');
    @endphp

    <x-slot:aksi>
        <x-tombol :href="route('admin.topup.index')" variant="hantu" ukuran="kecil" ikon="panah-kiri">Antrean</x-tombol>
    </x-slot:aksi>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-kartu judul="Bukti transfer" class="lg:col-span-2" padat>
            @if ($topup->bukti_transfer)
                @if ($pdf)
                    <x-tombol :href="route('berkas.topup', $topup)" target="_blank" variant="sekunder" ikon="dokumen">Buka PDF bukti transfer</x-tombol>
                @else
                    <a href="{{ route('berkas.topup', $topup) }}" target="_blank">
                        <img src="{{ route('berkas.topup', $topup) }}" alt="Bukti transfer {{ $topup->kode }}"
                             class="max-h-[32rem] w-full rounded-xl bg-slate-100 object-contain dark:bg-slate-800">
                    </a>
                @endif
            @else
                <x-kosong ikon="dokumen" judul="Tidak ada bukti" />
            @endif
        </x-kartu>

        <div class="space-y-6">
            <x-kartu judul="Rincian">
                <p class="text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ rupiah($topup->jumlah) }}</p>
                <dl class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <x-baris-info label="Status"><x-lencana-status :status="$topup->status" /></x-baris-info>
                    <x-baris-info label="Bank pengirim">{{ $topup->bank_pengirim }}</x-baris-info>
                    <x-baris-info label="Nama pengirim">{{ $topup->nama_pengirim }}</x-baris-info>
                    <x-baris-info label="Diajukan">{{ tanggal_id($topup->created_at) }}, {{ $topup->created_at->format('H:i') }}</x-baris-info>
                    <x-baris-info label="Saldo sekarang">{{ rupiah($topup->pengepul->saldo) }}</x-baris-info>
                    @if ($topup->verifikator)
                        <x-baris-info label="Diperiksa">{{ $topup->verifikator->name }}, {{ tanggal_id($topup->diverifikasi_pada) }}</x-baris-info>
                    @endif
                    @if ($topup->catatan_admin)
                        <x-baris-info label="Catatan">{{ $topup->catatan_admin }}</x-baris-info>
                    @endif
                </dl>
            </x-kartu>

            @if ($topup->status === StatusTopup::Menunggu)
                <x-kartu judul="Keputusan">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Pastikan dana <strong>{{ rupiah($topup->jumlah) }}</strong> dari <strong>{{ $topup->nama_pengirim }}</strong>
                        sudah masuk ke rekening {{ pengaturan('bank_nama') }} {{ pengaturan('bank_rekening') }}.
                    </p>
                    <div class="mt-4 space-y-2">
                        <form method="POST" action="{{ route('admin.topup.setujui', $topup) }}">
                            @csrf
                            <x-tombol type="submit" variant="primer" penuh ikon="cek">Dana masuk — setujui</x-tombol>
                        </form>
                        <x-tombol href="#modal-tolak" variant="bahaya-halus" penuh ikon="silang">Tolak</x-tombol>
                    </div>
                </x-kartu>
            @endif
        </div>
    </div>

    <x-modal id="modal-tolak" judul="Tolak top-up">
        <form method="POST" action="{{ route('admin.topup.tolak', $topup) }}" class="space-y-4">
            @csrf
            <x-kolom nama="catatan" label="Alasan" wajib placeholder="Contoh: dana belum masuk ke rekening" />
            <div class="flex justify-end gap-2">
                <x-tombol href="#_" variant="hantu">Batal</x-tombol>
                <x-tombol type="submit" variant="bahaya">Tolak</x-tombol>
            </div>
        </form>
    </x-modal>
</x-layouts.panel>
