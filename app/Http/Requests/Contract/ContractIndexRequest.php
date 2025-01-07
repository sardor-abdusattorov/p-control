<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Или добавьте логику проверки прав доступа, если необходимо
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'field' => ['in:name,short_name,value,status,created_at,'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }

}
