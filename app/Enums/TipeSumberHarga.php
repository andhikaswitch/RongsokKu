<?php

namespace App\Enums;

enum TipeSumberHarga: string
{
    case Pemerintah = 'pemerintah';
    case Asosiasi = 'asosiasi';
    case BankSampah = 'bank_sampah';
    case Pabrik = 'pabrik';
    case Bursa = 'bursa';
    case SurveiInternal = 'survei_internal';

    public function label(): string
    {
        return match ($this) {
            self::Pemerintah => 'Instansi Pemerintah',
            self::Asosiasi => 'Asosiasi Industri',
            self::BankSampah => 'Bank Sampah Induk',
            self::Pabrik => 'Pabrik Daur Ulang',
            self::Bursa => 'Bursa Komoditas',
            self::SurveiInternal => 'Survei Internal Tim',
        };
    }
}
