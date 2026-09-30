<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class KirimPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat_jemput' => ['required', 'string', 'max:255'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'jadwal_tanggal' => ['required', 'date', 'after:today', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'jadwal_sesi' => ['required', 'in:pagi,siang,sore'],
            'catatan_warga' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_tanggal.after' => 'Penjemputan paling cepat dijadwalkan besok.',
            'jadwal_tanggal.before_or_equal' => 'Penjemputan paling lambat dijadwalkan 30 hari dari sekarang.',
        ];
    }
}
