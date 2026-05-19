<?php

namespace App\Http\Requests\ContactSubCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactSubIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => ['nullable', 'string'],
            'info'        => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:contact_categories,id'],
            'status'      => ['nullable', 'in:0,1'],
            'field'       => ['nullable', 'in:title,info,status,category_id,created_at'],
            'order'       => ['nullable', 'in:asc,desc'],
            'perPage'     => ['nullable', 'numeric'],
        ];
    }
}
