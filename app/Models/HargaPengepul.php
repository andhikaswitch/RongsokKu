<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaPengepul extends Model
{
    protected $table = 'harga_pengepul';

    protected $fillable = [
        'profil_pengepul_id', 'kategori_sampah_id', 'harga_per_satuan',
        'min_berat', 'sedang_menerima', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_satuan' => 'decimal:2',
            'min_berat' => 'decimal:2',
            'sedang_menerima' => 'boolean',
        ];
    }

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(ProfilPengepul::class, 'profil_pengepul_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    /**
     * Posisi harga ini terhadap Indeks RongsokKu.
     * Dipakai untuk label peringatan di UI, bukan untuk memblokir.
     */
    public function posisiTerhadapIndeks(): ?array
    {
        $indeks = $this->kategori->indeksTerbaru();

        if (! $indeks || (float) $indeks->harga_median <= 0) {
            return null;
        }

        $selisih = round(
            (((float) $this->harga_per_satuan - (float) $indeks->harga_median) / (float) $indeks->harga_median) * 100,
            1
        );

        return [
            'persen' => $selisih,
            'median' => (float) $indeks->harga_median,
            'arah' => match (true) {
                $selisih >= 2 => 'atas',
                $selisih <= -2 => 'bawah',
                default => 'sesuai',
            },
        ];
    }
}
