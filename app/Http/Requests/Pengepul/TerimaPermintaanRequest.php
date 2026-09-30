<?php

namespace App\Http\Requests\Pengepul;

use Illuminate\Foundation\Http\FormRequest;

class TerimaPermintaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_tanggal' => ['nullable', 'date', 'after_or_equal:today'],
            'jadwal_sesi' => ['nullable', 'in:pagi,siang,sore'],
            'catatan_pengepul' => ['nullable', 'string', 'max:500'],
        ];
    }
}
