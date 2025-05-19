<?php

namespace App\Http\Requests\Contacts;

use Illuminate\Foundation\Http\FormRequest;

class ContactIndexRequest extends FormRequest
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
            'field' => ['in:title,email'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
