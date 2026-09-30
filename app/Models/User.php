<?php

namespace App\Models;

use App\Enums\PeranPengguna;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'peran', 'telepon', 'foto_profil',
        'wilayah_id', 'alamat_detail', 'latitude', 'longitude', 'aktif',
    ];

    protected $hidden = ['password', 'remember_token'];

    /** Samakan dengan nilai bawaan kolom agar model yang baru dibuat langsung konsisten. */
    protected $attributes = [
        'peran' => 'warga',
        'aktif' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'disuspend_pada' => 'datetime',
            'password' => 'hashed',
            'peran' => PeranPengguna::class,
            'aktif' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function profilPengepul(): HasOne
    {
        return $this->hasOne(ProfilPengepul::class);
    }

    public function permintaan(): HasMany
    {
        return $this->hasMany(PermintaanJemput::class, 'warga_id');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'warga_id');
    }

    public function lencana(): BelongsToMany
    {
        return $this->belongsToMany(Lencana::class, 'lencana_pengguna')
            ->withPivot('diraih_pada')
            ->withTimestamps();
    }

    public function isWarga(): bool
    {
        return $this->peran === PeranPengguna::Warga;
    }

    public function isPengepul(): bool
    {
        return $this->peran === PeranPengguna::Pengepul;
    }

    public function isAdmin(): bool
    {
        return $this->peran === PeranPengguna::Admin;
    }

    /** Total berat yang sudah diselamatkan warga ini dari TPA. */
    public function totalBeratDidaurUlang(): float
    {
        return (float) $this->permintaan()
            ->where('status', \App\Enums\StatusPermintaan::Selesai)
            ->sum('berat_final_kg');
    }

    public function inisial(): string
    {
        $bagian = preg_split('/\s+/', trim($this->name));
        $awal = mb_substr($bagian[0] ?? '?', 0, 1);
        $akhir = count($bagian) > 1 ? mb_substr(end($bagian), 0, 1) : '';

        return mb_strtoupper($awal.$akhir);
    }

    public function scopePeran(Builder $query, PeranPengguna $peran): Builder
    {
        return $query->where('peran', $peran);
    }
}
