<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'min:8', 'email'],
            'password' => ['required', 'min:8'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => __('msg.email.required'),
            'email.email' => __('msg.email.email'),
            'email.min' => __('msg.email.min'),
            'password.required' => __('msg.password.required'),
            'password.min' => __('msg.password.min'),
        ];
    }
}
