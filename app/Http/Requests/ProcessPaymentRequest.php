<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'result' => ['required', 'in:success,failed'],
        ];
    }

    public function messages(): array
    {
        return [
            'result.required' => 'Payment result is required.',
            'result.in' => 'Payment result must be either success or failed.',
        ];
    }
}