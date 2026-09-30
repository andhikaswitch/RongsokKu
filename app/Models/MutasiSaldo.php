<?php

namespace App\Models;

use App\Enums\JenisMutasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MutasiSaldo extends Model
{
    protected $table = 'mutasi_saldo';

    protected $fillable = [
        'profil_pengepul_id', 'jenis', 'jumlah', 'saldo_sebelum', 'saldo_sesudah',
        'referensi_type', 'referensi_id', 'keterangan', 'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => JenisMutasi::class,
            'jumlah' => 'decimal:2',
            'saldo_sebelum' => 'decimal:2',
            'saldo_sesudah' => 'decimal:2',
        ];
    }

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(ProfilPengepul::class, 'profil_pengepul_id');
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}
