<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:5'],
            'email' => ['required', 'min:8', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('msg.name.required'),
            'name.min' => __('msg.name.min'),
            'email.required' => __('msg.email.required'),
            'email.email' => __('msg.email.email'),
            'email.min' => __('msg.email.min'),
            'email.unique' => __('msg.email.unique'),
            'password.required' => __('msg.password.required'),
            'password.min' => __('msg.password.min'),
        ];
    }
}
