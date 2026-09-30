<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPermintaan extends Model
{
    protected $table = 'item_permintaan';

    protected $fillable = [
        'permintaan_jemput_id', 'kategori_sampah_id', 'estimasi_berat',
        'harga_estimasi_per_satuan', 'subtotal_estimasi',
        'berat_final', 'harga_final_per_satuan', 'subtotal_final',
        'foto_barang', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_berat' => 'decimal:2',
            'harga_estimasi_per_satuan' => 'decimal:2',
            'subtotal_estimasi' => 'decimal:2',
            'berat_final' => 'decimal:2',
            'harga_final_per_satuan' => 'decimal:2',
            'subtotal_final' => 'decimal:2',
        ];
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanJemput::class, 'permintaan_jemput_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    /** Selisih berat aktual terhadap taksiran warga, dalam persen. */
    public function selisihBeratPersen(): ?float
    {
        if ($this->berat_final === null || (float) $this->estimasi_berat <= 0) {
            return null;
        }

        return round(
            (((float) $this->berat_final - (float) $this->estimasi_berat) / (float) $this->estimasi_berat) * 100,
            1
        );
    }
}
