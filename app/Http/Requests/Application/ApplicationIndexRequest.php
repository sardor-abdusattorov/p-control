<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationIndexRequest extends FormRequest
{
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
            'search' => ['nullable', 'string', 'max:255'],
            'field' => ['nullable', 'in:title,user_id,project_id,status_id,type'],
            'order' => ['nullable', 'in:asc,desc'],
            'perPage' => ['nullable', 'numeric'],
            'project_id' => ['sometimes', 'nullable', 'integer', 'exists:projects,id'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'status_id' => ['sometimes', 'nullable', 'integer'],
            'type' => ['sometimes', 'nullable', 'integer'],
        ];
    }

}
