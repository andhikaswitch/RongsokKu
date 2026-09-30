<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pengaturan' => ['required', 'array'],
            'pengaturan.tarif_komisi' => ['required', 'numeric', 'between:0,50'],
            'pengaturan.saldo_minimum' => ['required', 'numeric', 'min:0'],
            'pengaturan.topup_minimum' => ['required', 'numeric', 'min:0'],
            'pengaturan.min_transaksi_indeks' => ['nullable', 'integer', 'min:1'],
            'pengaturan.ambang_peringatan_harga' => ['nullable', 'numeric', 'between:0,100'],
            'pengaturan.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pengaturan.tarif_komisi' => 'tarif komisi',
            'pengaturan.saldo_minimum' => 'saldo minimum',
            'pengaturan.topup_minimum' => 'nominal top-up minimum',
        ];
    }
}
