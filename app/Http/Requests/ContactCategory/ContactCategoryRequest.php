<?php

namespace App\Http\Requests\ContactCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'    => ['nullable', 'string'],
            'info'     => ['nullable', 'string'],
            'status'   => ['nullable', 'in:0,1'],
            'field'    => ['nullable', 'in:title,info,status,created_at'],
            'order'    => ['nullable', 'in:asc,desc'],
            'perPage'  => ['nullable', 'integer', 'min:1'],
        ];
    }
}
