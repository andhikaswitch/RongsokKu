<?php

namespace App\Services;

use App\Enums\StatusPermintaan;
use App\Models\PermintaanJemput;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanService
{
    /** @param  array{status?: ?string, pengepul?: ?string, dari?: ?string, sampai?: ?string, q?: ?string}  $filter */
    public function queryTransaksi(array $filter): Builder
    {
        $status = StatusPermintaan::tryFrom((string) ($filter['status'] ?? ''));

        return PermintaanJemput::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($filter['pengepul'] ?? null, fn ($q, $id) => $q->where('profil_pengepul_id', $id))
            ->when($filter['dari'] ?? null, fn ($q, $t) => $q->whereDate('created_at', '>=', $t))
            ->when($filter['sampai'] ?? null, fn ($q, $t) => $q->whereDate('created_at', '<=', $t))
            ->when($filter['q'] ?? null, fn ($q, $cari) => $q->where(fn ($w) => $w
                ->where('kode', 'like', "%{$cari}%")
                ->orWhereHas('warga', fn ($u) => $u->where('name', 'like', "%{$cari}%"))));
    }

    public function ringkasanTransaksi(array $filter): array
    {
        $selesai = $this->queryTransaksi($filter)->where('status', StatusPermintaan::Selesai);

        return [
            'jumlah' => $this->queryTransaksi($filter)->count(),
            'selesai' => $selesai->clone()->count(),
            'nilai' => (float) $selesai->clone()->sum('total_final'),
            'komisi' => (float) $selesai->clone()->sum('jumlah_komisi'),
            'berat' => (float) $selesai->clone()->sum('berat_final_kg'),
        ];
    }

    public function eksporCsv(array $filter): StreamedResponse
    {
        $nama = 'transaksi-rongsokku-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filter) {
            $keluaran = fopen('php://output', 'w');
            // BOM agar Excel membaca huruf Indonesia dengan benar.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, [
                'Kode', 'Tanggal Dibuat', 'Status', 'Warga', 'Pengepul', 'Kelurahan',
                'Estimasi Berat (kg)', 'Berat Final (kg)', 'Estimasi Total', 'Total Final',
                'Tarif Komisi (%)', 'Komisi', 'Selesai Pada',
            ], ';');

            $this->queryTransaksi($filter)
                ->with(['warga', 'pengepul', 'wilayah'])
                ->orderBy('id')
                ->chunk(200, function ($baris) use ($keluaran) {
                    foreach ($baris as $p) {
                        fputcsv($keluaran, [
                            $p->kode,
                            $p->created_at?->format('Y-m-d H:i'),
                            $p->status->label(),
                            $p->warga?->name,
                            $p->pengepul?->nama_usaha ?? '(terbuka)',
                            $p->wilayah?->nama,
                            $p->estimasi_berat_kg,
                            $p->berat_final_kg,
                            $p->estimasi_total,
                            $p->total_final,
                            $p->tarif_komisi,
                            $p->jumlah_komisi,
                            $p->selesai_pada?->format('Y-m-d H:i'),
                        ], ';');
                    }
                });

            fclose($keluaran);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
