<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractRelatedRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'field' => ['nullable', 'in:title,contract_number,budget_sum,status,currency_id'],
            'order' => ['nullable', 'in:asc,desc'],
            'perPage' => ['nullable', 'numeric'],
        ];
    }
}
