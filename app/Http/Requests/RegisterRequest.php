<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 'document' => 'required|string|max:255',
            // 'name' => 'required|string|max:255',
            // 'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // 'hour_start' => 'required|date_format:H:i:s',
            // 'hour_end' => 'required|date_format:H:i:s'
        ];
    }
}
