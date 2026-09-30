<?php

namespace App\Enums;

enum StatusPermintaan: string
{
    case Diajukan = 'diajukan';
    case Dijadwalkan = 'dijadwalkan';
    case Dijemput = 'dijemput';
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';
    case Dibatalkan = 'dibatalkan';
    case Sengketa = 'sengketa';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Menunggu Respons',
            self::Dijadwalkan => 'Dijadwalkan',
            self::Dijemput => 'Sedang Dijemput',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
            self::Sengketa => 'Sengketa',
        };
    }

    /** Kelas Tailwind untuk lencana status. */
    public function warna(): string
    {
        return match ($this) {
            self::Diajukan => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
            self::Dijadwalkan => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
            self::Dijemput => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-400/10 dark:text-indigo-300 dark:ring-indigo-400/30',
            self::MenungguKonfirmasi => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-400/10 dark:text-violet-300 dark:ring-violet-400/30',
            self::Selesai => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
            self::Ditolak, self::Dibatalkan => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-400/10 dark:text-slate-300 dark:ring-slate-400/30',
            self::Sengketa => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/30',
        };
    }

    public function selesaiPermanen(): bool
    {
        return in_array($this, [self::Selesai, self::Ditolak, self::Dibatalkan], true);
    }

    /** Urutan langkah untuk penanda progres di UI. */
    public function langkah(): int
    {
        return match ($this) {
            self::Diajukan => 1,
            self::Dijadwalkan => 2,
            self::Dijemput => 3,
            self::MenungguKonfirmasi => 4,
            self::Selesai => 5,
            default => 0,
        };
    }
}
