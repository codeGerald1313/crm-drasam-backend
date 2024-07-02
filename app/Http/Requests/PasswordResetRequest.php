<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo :attribute es requerido.',
            'email.email' => 'El :attribute debe ser una dirección de correo electrónico válida.',
            'email.exists' => 'El :attribute no existe en nuestros registros.',
        ];
    }

    // El método withValidator se llama despúes de que las reglas de validación se hayan aplicado.
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = User::where('email', $this->input('email'))->first();

            if ($user && $user->status === 0) {
                $validator->errors()->add('status', 'El usuario está deshabilitado');
            }
        });
    }
}
