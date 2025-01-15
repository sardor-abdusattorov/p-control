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
            'budget_sum' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'project_year' => 'required|date',
        ];
    }

}
