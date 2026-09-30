<?php

namespace App\Http\Requests\Umum;

use Illuminate\Foundation\Http\FormRequest;

class ProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'telepon' => ['required', 'string', 'regex:/^08[0-9]{8,12}$/'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'alamat_detail' => ['required', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return ['telepon.regex' => 'Nomor telepon harus diawali 08 dan terdiri dari 10 sampai 14 angka.'];
    }
}
