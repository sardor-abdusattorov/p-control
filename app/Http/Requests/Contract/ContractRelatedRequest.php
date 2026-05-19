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
            'field' => ['in:title,contract_number,budget_sum,status,currency_id'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
