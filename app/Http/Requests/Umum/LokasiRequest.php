<?php

namespace App\Http\Requests\Umum;

use Illuminate\Foundation\Http\FormRequest;

class LokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Titik harus berada di wilayah layanan: Kabupaten Karawang dengan sedikit kelonggaran. */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-6.75,-5.80'],
            'longitude' => ['required', 'numeric', 'between:106.90,107.80'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'Titik lokasi harus berada di wilayah Kabupaten Karawang.',
            'longitude.between' => 'Titik lokasi harus berada di wilayah Kabupaten Karawang.',
        ];
    }
}
