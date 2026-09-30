<?php

namespace App\Models;

use App\Enums\TipeSumberHarga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumberHargaPasar extends Model
{
    protected $table = 'sumber_harga_pasar';

    protected $fillable = [
        'kategori_sampah_id', 'harga_min', 'harga_max', 'sumber_nama',
        'sumber_tipe', 'sumber_url', 'dokumen_bukti',
        'berlaku_mulai', 'berlaku_sampai', 'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'sumber_tipe' => TipeSumberHarga::class,
            'harga_min' => 'decimal:2',
            'harga_max' => 'decimal:2',
            'berlaku_mulai' => 'date',
            'berlaku_sampai' => 'date',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
