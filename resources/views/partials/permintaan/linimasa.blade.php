@php
    use App\Enums\StatusPermintaan;
@endphp
{{-- Linimasa status permintaan. Butuh: $permintaan --}}
@php

    $p = $permintaan;
    $tahap = [
        ['Diajukan', $p->created_at, 'Permintaan dikirim warga'],
        ['Diterima & dijadwalkan', $p->diterima_pada, $p->jadwal_tanggal ? 'Jemput '.tanggal_id($p->jadwal_tanggal).', sesi '.$p->jadwal_sesi : null],
        ['Pengepul berangkat', $p->dijemput_pada, null],
        ['Ditimbang di lokasi', $p->ditimbang_pada, $p->berat_final_kg ? berat($p->berat_final_kg).' · '.rupiah($p->total_final) : null],
        ['Selesai', $p->selesai_pada, $p->jumlah_komisi ? null : null],
    ];

    $berakhir = in_array($p->status, [StatusPermintaan::Ditolak, StatusPermintaan::Dibatalkan, StatusPermintaan::Sengketa], true);
@endphp

<ol class="relative space-y-5 border-l-2 border-slate-100 pl-6 dark:border-slate-800">
    @foreach ($tahap as [$label, $waktu, $keterangan])
        <li class="relative">
            <span @class([
                'absolute -left-[33px] grid size-4 place-items-center rounded-full ring-4 ring-white dark:ring-slate-900',
                'bg-merk-500' => $waktu,
                'bg-slate-200 dark:bg-slate-700' => ! $waktu,
            ])></span>
            <p @class([
                'text-sm font-semibold',
                'text-slate-900 dark:text-white' => $waktu,
                'text-slate-400' => ! $waktu,
            ])>{{ $label }}</p>
            @if ($waktu)
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ tanggal_id($waktu) }}, {{ $waktu->format('H:i') }}
                    @if ($keterangan) · {{ $keterangan }} @endif
                </p>
            @endif
        </li>
    @endforeach

    @if ($berakhir)
        <li class="relative">
            <span class="absolute -left-[33px] grid size-4 place-items-center rounded-full bg-rose-500 ring-4 ring-white dark:ring-slate-900"></span>
            <p class="text-sm font-semibold text-rose-700 dark:text-rose-400">{{ $p->status->label() }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                @if ($p->status === StatusPermintaan::Ditolak)
                    Alasan: {{ $p->alasan_penolakan }}
                @elseif ($p->status === StatusPermintaan::Dibatalkan)
                    Oleh {{ $p->dibatalkan_oleh }}{{ $p->alasan_pembatalan ? ' — '.$p->alasan_pembatalan : '' }}
                @else
                    Menunggu keputusan admin
                @endif
            </p>
        </li>
    @endif
</ol>
