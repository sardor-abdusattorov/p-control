<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'contract_number' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'project_id' => 'required|integer',
            'recipients' => 'nullable|array',
            'application_id' => 'required|integer',
            'budget_sum' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currency,id',
            'deadline' => 'required|date',
            'application_type' => 'required|integer',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $applicationType = $this->input('application_type');

            if ($applicationType == 2) {
                if (!empty($this->input('recipients'))) {
                    $validator->errors()->add('recipients', 'Для служебной записки не требуется список получателей.');
                }
            } else {
                if (empty($this->input('recipients'))) {
                    $validator->errors()->add('recipients', 'Необходимо выбрать получателей.');
                }
            }
        });
    }
}
