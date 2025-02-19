<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractUpdateRequest extends FormRequest
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
            'contract_number' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'application_id' => 'required|integer',
            'budget_sum' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currency,id',
            'deadline' => 'required|date',
        ];
    }
}
