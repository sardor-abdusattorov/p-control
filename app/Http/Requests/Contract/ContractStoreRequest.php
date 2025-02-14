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
            'recipients' => 'nullable|array', // По умолчанию делаем nullable
            'application_id' => 'nullable|integer',
            'budget_sum' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currency,id',
            'deadline' => 'required|date',
            'application_type' => 'required|integer', // Добавляем проверку типа заявки
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $applicationType = $this->input('application_type');

            if ($applicationType == 2) {
                // Если type = 2 (служебная записка), recipients должны быть пустыми
                if (!empty($this->input('recipients'))) {
                    $validator->errors()->add('recipients', 'Для служебной записки не требуется список получателей.');
                }
            } else {
                // Если type ≠ 2, recipients должны быть обязательными
                if (empty($this->input('recipients'))) {
                    $validator->errors()->add('recipients', 'Необходимо выбрать получателей.');
                }
            }
        });
    }
}
