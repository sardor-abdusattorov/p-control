<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
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
