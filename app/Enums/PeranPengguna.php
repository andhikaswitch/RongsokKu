<?php

namespace App\Enums;

enum PeranPengguna: string
{
    case Warga = 'warga';
    case Pengepul = 'pengepul';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::Pengepul => 'Pengepul',
            self::Admin => 'Admin',
        };
    }

    public function beranda(): string
    {
        return match ($this) {
            self::Warga => 'warga.dashboard',
            self::Pengepul => 'pengepul.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}
