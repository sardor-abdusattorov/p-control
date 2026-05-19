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
            'field' => ['in:title,project_number,category_id,sort,status_id'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
