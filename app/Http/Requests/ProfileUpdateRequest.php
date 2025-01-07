<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'telegram_id' => 'nullable|string|max:255',
            'recipients' => 'nullable|array',
            'recipients.*' => 'integer|exists:users,id',
        ];
    }
}
