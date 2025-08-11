<?php

namespace App\Http\Requests\ContactSubCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactSubUpdateRequest extends FormRequest
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
            'category_id' => 'required|integer|exists:contact_categories,id',
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
