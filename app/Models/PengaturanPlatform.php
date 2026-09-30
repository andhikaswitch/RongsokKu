<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PengaturanPlatform extends Model
{
    protected $table = 'pengaturan_platform';

    protected $fillable = ['kunci', 'nilai', 'tipe', 'grup', 'label', 'keterangan'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('pengaturan_platform'));
        static::deleted(fn () => Cache::forget('pengaturan_platform'));
    }

    /** @return array<string, string|null> */
    public static function semua(): array
    {
        return Cache::rememberForever(
            'pengaturan_platform',
            fn () => static::pluck('nilai', 'kunci')->all()
        );
    }

    public static function ambil(string $kunci, mixed $bawaan = null): mixed
    {
        return static::semua()[$kunci] ?? $bawaan;
    }
}
