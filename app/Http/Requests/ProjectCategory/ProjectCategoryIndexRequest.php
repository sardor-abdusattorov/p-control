<?php

namespace App\Http\Requests\ProjectCategory;

use Illuminate\Foundation\Http\FormRequest;

class ProjectCategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => ['nullable', 'string'],
            'year'    => ['nullable', 'integer'],
            'status'  => ['nullable', 'in:0,1'],
            'field'   => ['nullable', 'in:title,sort,year,status,created_at'],
            'order'   => ['nullable', 'in:asc,desc'],
            'perPage' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
