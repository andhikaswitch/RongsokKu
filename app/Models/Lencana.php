<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lencana extends Model
{
    protected $table = 'lencana';

    protected $fillable = [
        'nama', 'slug', 'ikon', 'deskripsi', 'syarat_berat_kg', 'warna', 'urutan',
    ];

    protected function casts(): array
    {
        return ['syarat_berat_kg' => 'decimal:2'];
    }

    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lencana_pengguna')
            ->withPivot('diraih_pada')
            ->withTimestamps();
    }
}
