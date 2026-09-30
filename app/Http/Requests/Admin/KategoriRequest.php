<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'induk_id' => ['nullable', 'exists:kategori_sampah,id'],
            'nama' => ['required', 'string', 'max:100'],
            'ikon' => ['nullable', 'string', 'max:16'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'satuan' => ['required', 'in:kg,pcs'],
            'harga_acuan_min' => ['nullable', 'numeric', 'min:0'],
            'harga_acuan_max' => ['nullable', 'numeric', 'gte:harga_acuan_min'],
            'faktor_co2_per_kg' => ['required', 'numeric', 'min:0', 'max:100'],
            'limbah_b3' => ['nullable', 'boolean'],
            'peringatan_b3' => ['nullable', 'required_if:limbah_b3,1', 'string', 'max:1000'],
            'aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'induk_id' => 'golongan induk',
            'harga_acuan_min' => 'harga acuan minimum',
            'harga_acuan_max' => 'harga acuan maksimum',
            'faktor_co2_per_kg' => 'faktor CO2',
            'peringatan_b3' => 'peringatan B3',
        ];
    }
}
