<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LogAudit extends Model
{
    protected $table = 'log_audit';

    protected $fillable = [
        'user_id', 'aksi', 'subjek_type', 'subjek_id',
        'nilai_lama', 'nilai_baru', 'alamat_ip', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'nilai_lama' => 'array',
            'nilai_baru' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjek(): MorphTo
    {
        return $this->morphTo();
    }
}
