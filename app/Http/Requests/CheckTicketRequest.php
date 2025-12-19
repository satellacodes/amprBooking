<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public access
    }

    public function rules(): array
    {
        return [
            'unit_number' => ['required', 'string'],
            'access_code' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_number.required' => 'Nomor Unit wajib diisi.',
            'access_code.required' => 'PIN wajib diisi.',
        ];
    }
}
