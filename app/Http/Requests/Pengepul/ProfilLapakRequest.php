<?php

namespace App\Http\Requests\Pengepul;

use Illuminate\Foundation\Http\FormRequest;

class ProfilLapakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_usaha' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i', 'after:jam_buka'],
            'radius_layanan_km' => ['required', 'integer', 'between:1,50'],
            'sedang_menerima' => ['nullable', 'boolean'],
            'foto_lapak' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
