<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndeksHarga extends Model
{
    protected $table = 'indeks_harga';

    /** Indeks baru ditampilkan setelah ada cukup transaksi nyata. */
    public const MIN_TRANSAKSI = 5;

    protected $fillable = [
        'kategori_sampah_id', 'wilayah_id', 'tanggal', 'harga_median',
        'harga_min', 'harga_max', 'harga_rata', 'jumlah_transaksi',
        'total_berat_kg', 'perubahan_persen',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'harga_median' => 'decimal:2',
            'harga_min' => 'decimal:2',
            'harga_max' => 'decimal:2',
            'harga_rata' => 'decimal:2',
            'total_berat_kg' => 'decimal:2',
            'perubahan_persen' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function cukupData(): bool
    {
        return $this->jumlah_transaksi >= self::MIN_TRANSAKSI;
    }
}
