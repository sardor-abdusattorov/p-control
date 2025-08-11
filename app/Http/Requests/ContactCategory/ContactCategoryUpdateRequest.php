<?php

namespace App\Http\Requests\ContactCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'info' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
