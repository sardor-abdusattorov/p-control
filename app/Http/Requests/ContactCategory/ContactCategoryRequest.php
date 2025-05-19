<?php

namespace App\Http\Requests\ContactCategory;

use Illuminate\Foundation\Http\FormRequest;

class ContactCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Или добавьте логику проверки прав доступа, если необходимо
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
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
