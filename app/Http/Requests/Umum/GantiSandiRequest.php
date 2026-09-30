<?php

namespace App\Http\Requests\Umum;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class GantiSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
