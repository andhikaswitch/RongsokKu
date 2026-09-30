<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class TambahBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_sampah_id' => ['required', 'integer', 'exists:kategori_sampah,id'],
            'estimasi_berat' => ['required', 'numeric', 'min:0.1', 'max:5000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:200'],
        ];
    }
}
