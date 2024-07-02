<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->input('id');

        $rules = [
            'name' => 'required|string|max:255',
            
            'email' => [
                'required',
                'email'
            ],
            'role' => 'required|string'
        ];

        if (!$id) {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;

    }

    public function messages(): array
    {
		return [
			'name.required' => 'El usuario es requerido',
			'email.required' => 'Email obligatorio',
            'email.unique' => 'El email ya exite',
			'password.required' => 'Contraseña requerida'
		];
	}
}
