<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'contract_number' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'field'  => ['nullable', 'in:title,contract_number,user_id,status,currency_id'],
            'order'  => ['nullable', 'in:asc,desc'],
            'perPage' => ['nullable', 'numeric'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'integer', 'in:1,2,3,-1'],
            'currency_id' => ['nullable', 'exists:currency,id'],
            'approval_filter' => ['nullable', 'string', 'in:approved_by_me,not_approved_by_me'],
        ];
    }
}
