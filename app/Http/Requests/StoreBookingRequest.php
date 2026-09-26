<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'trial_class_id' => ['required', 'exists:trial_classes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Selected student does not exist.',
            'trial_class_id.required' => 'Please select a trial class.',
            'trial_class_id.exists' => 'Selected trial class does not exist.',
        ];
    }
}