<?php

namespace App\Models;

use App\Enums\StatusTopup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanTopup extends Model
{
    protected $table = 'permintaan_topup';

    protected $fillable = [
        'kode', 'profil_pengepul_id', 'jumlah', 'bank_pengirim',
        'nama_pengirim', 'bukti_transfer', 'status', 'catatan_admin',
        'diverifikasi_oleh', 'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusTopup::class,
            'jumlah' => 'decimal:2',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(ProfilPengepul::class, 'profil_pengepul_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }
}
