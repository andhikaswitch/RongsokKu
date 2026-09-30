<?php

namespace App\Http\Requests\Pengepul;

use Illuminate\Foundation\Http\FormRequest;

class TimbangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hasil' => ['required', 'array'],
            'hasil.*.berat_final' => ['required', 'numeric', 'min:0', 'max:5000'],
            'hasil.*.harga_final' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'catatan_pengepul' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['hasil.*.berat_final' => 'berat aktual', 'hasil.*.harga_final' => 'harga akhir'];
    }
}
