<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectsStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_number' => 'nullable|string|max:255',
            'title' => 'required|string',
            'category_id' => 'nullable|exists:project_categories,id',
            'sort' => 'nullable|integer|min:0',
            'status_id' => 'nullable|integer',
        ];
    }
}
