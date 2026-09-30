<?php

namespace App\Enums;

enum JenisMutasi: string
{
    case Topup = 'topup';
    case Komisi = 'komisi';
    case Refund = 'refund';
    case Penyesuaian = 'penyesuaian';

    public function label(): string
    {
        return match ($this) {
            self::Topup => 'Isi Saldo',
            self::Komisi => 'Potongan Komisi',
            self::Refund => 'Pengembalian',
            self::Penyesuaian => 'Penyesuaian Admin',
        };
    }

    public function menambah(): bool
    {
        return in_array($this, [self::Topup, self::Refund], true);
    }
}
