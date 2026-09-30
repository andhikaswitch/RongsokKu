<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sengketa extends Model
{
    protected $table = 'sengketa';

    protected $fillable = [
        'kode', 'permintaan_jemput_id', 'dilaporkan_oleh', 'alasan',
        'deskripsi', 'bukti', 'status', 'resolusi',
        'ditangani_oleh', 'ditangani_pada',
    ];

    protected function casts(): array
    {
        return ['ditangani_pada' => 'datetime'];
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanJemput::class, 'permintaan_jemput_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh');
    }

    public function penangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }
}
