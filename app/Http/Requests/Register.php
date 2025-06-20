<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ];
    }
    public function attributes()
    {
        return [
            'email' => __('main.email'),
            'password' => __('main.password'),
            'name' => __('main.name'),
            'phone' => __('main.phone'),
        ];
    }
}
