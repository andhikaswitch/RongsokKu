<?php

namespace App\Models;

use App\Enums\StatusPermintaan;
use App\Enums\StatusVerifikasi;
use App\Support\Haversine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilPengepul extends Model
{
    protected $table = 'profil_pengepul';

    protected $fillable = [
        'user_id', 'nama_usaha', 'slug', 'deskripsi', 'foto_lapak', 'foto_ktp',
        'status_verifikasi', 'izin_b3', 'jam_buka', 'jam_tutup',
        'radius_layanan_km', 'sedang_menerima',
    ];

    protected function casts(): array
    {
        return [
            'status_verifikasi' => StatusVerifikasi::class,
            'diverifikasi_pada' => 'datetime',
            'izin_b3' => 'boolean',
            'sedang_menerima' => 'boolean',
            'saldo' => 'decimal:2',
            'rating_rata' => 'decimal:2',
            'total_berat_kg' => 'decimal:2',
            'skor_kepatuhan_harga' => 'decimal:2',
            'tingkat_penerimaan' => 'decimal:2',
            'ketepatan_waktu' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function harga(): HasMany
    {
        return $this->hasMany(HargaPengepul::class);
    }

    public function permintaan(): HasMany
    {
        return $this->hasMany(PermintaanJemput::class);
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiSaldo::class);
    }

    public function topup(): HasMany
    {
        return $this->hasMany(PermintaanTopup::class);
    }

    public function terverifikasi(): bool
    {
        return $this->status_verifikasi === StatusVerifikasi::Terverifikasi;
    }

    public function sedangBuka(): bool
    {
        $sekarang = now()->format('H:i:s');

        return $this->sedang_menerima
            && $sekarang >= $this->jam_buka
            && $sekarang <= $this->jam_tutup;
    }

    public function saldoCukup(): bool
    {
        return $this->saldo >= (float) pengaturan('saldo_minimum', 10000);
    }

    /** Jarak ke sebuah titik, dalam km. Null bila koordinat belum lengkap. */
    public function jarakKe(?float $lat, ?float $lng): ?float
    {
        if ($lat === null || $lng === null || ! $this->user?->latitude) {
            return null;
        }

        return Haversine::jarak(
            (float) $this->user->latitude,
            (float) $this->user->longitude,
            $lat,
            $lng
        );
    }

    /** Label mutu untuk skor kepatuhan harga. */
    public function mutuKepatuhan(): array
    {
        $skor = (float) $this->skor_kepatuhan_harga;

        return match (true) {
            $skor >= 95 => ['label' => 'Sangat Baik', 'warna' => 'emerald', 'titik' => '🟢'],
            $skor >= 85 => ['label' => 'Baik', 'warna' => 'lime', 'titik' => '🟢'],
            $skor >= 70 => ['label' => 'Cukup', 'warna' => 'amber', 'titik' => '🟡'],
            default => ['label' => 'Hati-hati', 'warna' => 'rose', 'titik' => '🔴'],
        };
    }

    public function transaksiSelesai(): int
    {
        return $this->permintaan()->where('status', StatusPermintaan::Selesai)->count();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeSudahTerverifikasi(Builder $query): Builder
    {
        return $query->where('status_verifikasi', StatusVerifikasi::Terverifikasi);
    }

    public function scopeMenerima(Builder $query): Builder
    {
        return $query->where('sedang_menerima', true);
    }
}
