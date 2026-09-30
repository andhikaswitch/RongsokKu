<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = ['induk_id', 'nama', 'tingkat', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    /** Contoh keluaran: "Nagasari, Karawang Barat". */
    public function namaLengkap(): string
    {
        return $this->induk
            ? "{$this->nama}, {$this->induk->nama}"
            : $this->nama;
    }

    public function scopeKelurahan(Builder $query): Builder
    {
        return $query->where('tingkat', 'kelurahan');
    }

    public function scopeKecamatan(Builder $query): Builder
    {
        return $query->where('tingkat', 'kecamatan');
    }
}
