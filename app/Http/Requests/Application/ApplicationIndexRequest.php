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
            'field' => ['in:title,user_id,project_id,status_id,type'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
