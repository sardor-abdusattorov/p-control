<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => 'required',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'telegram_id' => 'nullable|string|max:255',
            'recipients' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ];
    }
}
