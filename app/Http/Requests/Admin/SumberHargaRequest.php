<?php

namespace App\Http\Requests\Admin;

use App\Enums\TipeSumberHarga;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SumberHargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_sampah_id' => ['required', 'exists:kategori_sampah,id'],
            'harga_min' => ['required', 'numeric', 'min:0'],
            'harga_max' => ['required', 'numeric', 'gte:harga_min'],
            'sumber_nama' => ['required', 'string', 'max:150'],
            'sumber_tipe' => ['required', Rule::enum(TipeSumberHarga::class)],
            'sumber_url' => ['nullable', 'url', 'max:255'],
            'dokumen_bukti' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'berlaku_mulai' => ['required', 'date'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
        ];
    }
}
