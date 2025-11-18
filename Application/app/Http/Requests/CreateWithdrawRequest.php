<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account' => ['required', 'exists:bank_accounts,id'],
            'pix_id' => ['required', 'exists:pixes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
