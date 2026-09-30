<?php

namespace App\Http\Requests\Pengepul;

use Illuminate\Foundation\Http\FormRequest;

class TopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'bank_pengirim' => ['required', 'string', 'max:50'],
            'nama_pengirim' => ['required', 'string', 'max:100'],
            'bukti_transfer' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ];
    }
}
