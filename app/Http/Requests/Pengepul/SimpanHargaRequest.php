<?php

namespace App\Http\Requests\Pengepul;

use Illuminate\Foundation\Http\FormRequest;

class SimpanHargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'harga' => ['array'],
            'harga.*.harga' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'harga.*.min_berat' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'harga.*.menerima' => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return ['harga.*.harga' => 'harga', 'harga.*.min_berat' => 'berat minimum'];
    }
}
