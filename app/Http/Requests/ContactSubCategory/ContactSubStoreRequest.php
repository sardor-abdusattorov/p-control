<?php

namespace App\Http\Requests\ContactSubCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactSubStoreRequest extends FormRequest
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
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:contact_categories,id'],
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
