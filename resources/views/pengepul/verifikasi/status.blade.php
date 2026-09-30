@php
    use App\Enums\StatusVerifikasi;
@endphp
<x-layouts.panel judul="Status Verifikasi" :keterangan="$profil->nama_usaha">
    @section('judul', 'Status Verifikasi')

    @php
        $s = $profil->status_verifikasi;
    @endphp

    <x-kartu class="max-w-2xl">
        <div class="py-6 text-center">
            <span @class([
                'mx-auto grid size-16 place-items-center rounded-2xl',
                'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' => $s === StatusVerifikasi::Terverifikasi,
                'bg-amber-50 text-amber-600 dark:bg-amber-500/10' => $s === StatusVerifikasi::Menunggu,
                'bg-rose-50 text-rose-600 dark:bg-rose-500/10' => $s === StatusVerifikasi::Ditolak,
                'bg-slate-100 text-slate-500 dark:bg-slate-800' => $s === StatusVerifikasi::Draf,
            ])>
                <x-ikon :nama="match ($s) { StatusVerifikasi::Terverifikasi => 'cek-lingkar', StatusVerifikasi::Menunggu => 'jam', StatusVerifikasi::Ditolak => 'silang', default => 'dokumen' }" ukuran="size-8" />
            </span>

            <div class="mt-5"><x-lencana-status :status="$s" /></div>

            <h2 class="mt-3 text-xl font-bold text-slate-900 dark:text-white">
                @switch($s)
                    @case(StatusVerifikasi::Terverifikasi) Lapak Anda sudah terverifikasi @break
                    @case(StatusVerifikasi::Menunggu) Berkas sedang diperiksa @break
                    @case(StatusVerifikasi::Ditolak) Verifikasi ditolak @break
                    @default Berkas belum diajukan
                @endswitch
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                @switch($s)
                    @case(StatusVerifikasi::Terverifikasi)
                        Diverifikasi pada {{ tanggal_id($profil->diverifikasi_pada) }}. Pasang daftar harga agar warga bisa memilih Anda.
                        @break
                    @case(StatusVerifikasi::Menunggu)
                        Admin biasanya memeriksa dalam 1–2 hari kerja. Sambil menunggu, isi saldo agar siap menerima permintaan.
                        @break
                    @case(StatusVerifikasi::Ditolak)
                        Alasan: {{ $profil->alasan_penolakan }}
                        @break
                    @default
                        Unggah KTP dan foto lapak untuk mulai menerima permintaan dari warga.
                @endswitch
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @if (in_array($s, [StatusVerifikasi::Draf, StatusVerifikasi::Ditolak], true))
                    <x-tombol :href="route('pengepul.verifikasi.form')" variant="primer" ikon="unggah">
                        {{ $s === StatusVerifikasi::Ditolak ? 'Ajukan ulang' : 'Lengkapi berkas' }}
                    </x-tombol>
                @elseif ($s === StatusVerifikasi::Terverifikasi)
                    <x-tombol :href="route('pengepul.harga.index')" variant="primer" ikon="grafik">Atur daftar harga</x-tombol>
                @else
                    <x-tombol :href="route('pengepul.dompet.index')" variant="primer" ikon="dompet">Isi saldo</x-tombol>
                @endif
                <x-tombol :href="route('pengepul.dashboard')" variant="sekunder">Ke dashboard</x-tombol>
            </div>
        </div>
    </x-kartu>
</x-layouts.panel>
