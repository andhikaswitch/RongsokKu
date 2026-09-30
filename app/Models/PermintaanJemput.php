<?php

namespace App\Models;

use App\Enums\StatusPermintaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanJemput extends Model
{
    protected $table = 'permintaan_jemput';

    protected $fillable = [
        'kode', 'warga_id', 'profil_pengepul_id', 'permintaan_terbuka', 'status',
        'wilayah_id', 'alamat_jemput', 'latitude', 'longitude', 'jarak_km',
        'jadwal_tanggal', 'jadwal_sesi', 'estimasi_total', 'estimasi_berat_kg',
        'catatan_warga',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPermintaan::class,
            'permintaan_terbuka' => 'boolean',
            'jadwal_tanggal' => 'date',
            'estimasi_total' => 'decimal:2',
            'total_final' => 'decimal:2',
            'estimasi_berat_kg' => 'decimal:2',
            'berat_final_kg' => 'decimal:2',
            'tarif_komisi' => 'decimal:2',
            'jumlah_komisi' => 'decimal:2',
            'jarak_km' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'diterima_pada' => 'datetime',
            'dijemput_pada' => 'datetime',
            'ditimbang_pada' => 'datetime',
            'selesai_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(ProfilPengepul::class, 'profil_pengepul_id');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function item(): HasMany
    {
        return $this->hasMany(ItemPermintaan::class);
    }

    public function ulasan(): HasOne
    {
        return $this->hasOne(Ulasan::class);
    }

    public function sengketa(): HasOne
    {
        return $this->hasOne(Sengketa::class);
    }

    /**
     * Selisih antara harga yang dipajang saat pengajuan dan harga yang
     * benar-benar dibayar. Negatif berarti warga dibayar lebih rendah.
     */
    public function selisihHargaPersen(): ?float
    {
        if (! $this->total_final || ! $this->estimasi_total || ! $this->berat_final_kg) {
            return null;
        }

        $seharusnya = $this->item->sum(
            fn (ItemPermintaan $item) => (float) ($item->berat_final ?? 0) * (float) $item->harga_estimasi_per_satuan
        );

        if ($seharusnya <= 0) {
            return null;
        }

        return round((((float) $this->total_final - $seharusnya) / $seharusnya) * 100, 2);
    }

    public function totalCo2(): float
    {
        return (float) $this->item->sum(
            fn (ItemPermintaan $item) => (float) ($item->berat_final ?? 0) * (float) $item->kategori->faktor_co2_per_kg
        );
    }

    public function bisaDiulas(): bool
    {
        return $this->status === StatusPermintaan::Selesai && ! $this->ulasan;
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public function scopeStatus(Builder $query, StatusPermintaan ...$status): Builder
    {
        return $query->whereIn('status', array_map(fn ($s) => $s->value, $status));
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', StatusPermintaan::Selesai);
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->whereIn('status', [
            StatusPermintaan::Diajukan->value,
            StatusPermintaan::Dijadwalkan->value,
            StatusPermintaan::Dijemput->value,
            StatusPermintaan::MenungguKonfirmasi->value,
        ]);
    }
}
