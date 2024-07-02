<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RequestContactAsign extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'num_phone' => [
                'required',
                Rule::unique('contacts')->ignore($id)
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'num_phone.required' => 'El número de telefono es obligatorio',
            'num_phone.unique' => 'Este número ya ha sido registrado',
        ];
    }
}
