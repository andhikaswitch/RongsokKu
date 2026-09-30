<?php

namespace App\Services;

use App\Models\LogAudit;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function catat(string $aksi, ?Model $subjek = null, array $lama = [], array $baru = []): LogAudit
    {
        $request = request();

        return LogAudit::create([
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'subjek_type' => $subjek ? $subjek::class : null,
            'subjek_id' => $subjek?->getKey(),
            'nilai_lama' => $lama ?: null,
            'nilai_baru' => $baru ?: null,
            'alamat_ip' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 250) : null,
        ]);
    }
}
