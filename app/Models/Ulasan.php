<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ulasan extends Model
{
    protected $table = 'ulasan';

    protected $fillable = [
        'permintaan_jemput_id', 'warga_id', 'profil_pengepul_id',
        'rating', 'komentar', 'balasan', 'dibalas_pada',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'dibalas_pada' => 'datetime',
        ];
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanJemput::class, 'permintaan_jemput_id');
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(ProfilPengepul::class, 'profil_pengepul_id');
    }
}
