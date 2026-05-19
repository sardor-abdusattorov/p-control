<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => 'required|unique:users,email,' . $this->user,
            'password' => [
                'nullable',
                'confirmed',
                'min:6',
            ],
            'password_confirmation' => 'sometimes|required_with:password|same:password',
            'role'                  => ['required'],
        ];
    }
}
