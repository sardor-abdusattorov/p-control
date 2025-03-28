<?php

namespace App\Http\Requests\Contract;

use App\Models\Contract;
use App\Models\Currency;
use App\Models\User;
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
            'application_id' => 'required|integer',
            'budget_sum' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currency,id',
            'deadline' => 'required|date',
            'application_type' => 'required|integer',
            'recipients' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (!is_array($value) || count($value) < 2) {
                        $fail('Необходимо выбрать минимум 2 получателя.');
                    }

                    $currencyId = $this->input('currency_id');
                    $currency = Currency::find($currencyId);

                    if ($currency && strtoupper($currency->short_name) !== 'UZS') {
                        $hasAccountant = User::whereIn('id', $value)
                            ->where('department_id', 9)
                            ->exists();

                        if (!$hasAccountant) {
                            $fail('При выборе валюты, отличной от UZS, необходимо выбрать получателя из бухгалтерии.');
                        }
                    }
                }
            ],
        ];
    }
}
