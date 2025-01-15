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
            'title' => 'required|string|max:255',
            'budget_sum' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'currency_id' => 'nullable|exists:currency,id',
            'deadline' => 'required|date',
            'project_year' => 'required|date',
        ];
    }
}
