<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSampah extends Model
{
    protected $table = 'kategori_sampah';

    protected $fillable = [
        'induk_id', 'nama', 'slug', 'ikon', 'deskripsi', 'satuan',
        'harga_acuan_min', 'harga_acuan_max', 'faktor_co2_per_kg',
        'limbah_b3', 'peringatan_b3', 'aktif', 'urutan',
    ];

    protected function casts(): array
    {
        return [
            'harga_acuan_min' => 'decimal:2',
            'harga_acuan_max' => 'decimal:2',
            'faktor_co2_per_kg' => 'decimal:3',
            'limbah_b3' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id')->orderBy('urutan');
    }

    public function hargaPengepul(): HasMany
    {
        return $this->hasMany(HargaPengepul::class);
    }

    public function indeksHarga(): HasMany
    {
        return $this->hasMany(IndeksHarga::class);
    }

    public function sumberHarga(): HasMany
    {
        return $this->hasMany(SumberHargaPasar::class);
    }

    /** Acuan resmi yang masih berlaku hari ini. */
    public function acuanBerlaku(): ?SumberHargaPasar
    {
        return $this->sumberHarga()
            ->where('berlaku_mulai', '<=', now())
            ->where(fn ($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now()))
            ->latest('berlaku_mulai')
            ->first();
    }

    public function indeksTerbaru(): ?IndeksHarga
    {
        return $this->indeksHarga()->whereNull('wilayah_id')->latest('tanggal')->first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function scopeUtama(Builder $query): Builder
    {
        return $query->whereNull('induk_id');
    }

    public function scopeTurunan(Builder $query): Builder
    {
        return $query->whereNotNull('induk_id');
    }
}
