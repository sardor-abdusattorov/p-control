<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectsIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'field' => ['in:title,project_number,category_id,sort,status_id'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
