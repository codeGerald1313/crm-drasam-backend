<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => 'sometimes|integer', // El campo id es opcional y debe ser un entero
            'name' => 'required|string|max:255',
            'num_phone' => 'required|string|max:20',
            /*'document' => [
                'required',
                'max:20',
                Rule::unique('contacts')->ignore($id)
            ],
            
            'document_id' => 'required|exists:documents,id',
            'birthdate' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('contacts')->ignore($id)
            ],
            'country_code' => 'required|string|max:5', */
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio',
            'num_phone.required' => 'El número de telefono es obligatorio',

            // document.required' => 'El campo documento es obligatorio',
            // 'birthdate.required' => 'La fecha de cumpleaños es requerida',
            // 'email.required' => 'El campo email es obligatorio',
            // 'email.unique' => 'El email ya exite',
            // 'document.unique' => 'El documento ya exite',
            // 'country_code.required' => 'El campo documento es obligatorio',
        ];
    }
}
