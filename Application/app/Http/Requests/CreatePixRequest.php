<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payer_name' => ['nullable', 'string'],
            'payer_document' => ['nullable', 'string'],
            'mock_header' => ['nullable', 'string'],
        ];
    }
}

