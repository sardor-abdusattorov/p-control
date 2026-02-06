<?php

namespace App\Http\Requests\ProjectCategory;

use Illuminate\Foundation\Http\FormRequest;

class ProjectCategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'  => ['required', 'string', 'max:255'],
            'sort'   => ['nullable', 'integer', 'min:0'],
            'year'   => ['required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
