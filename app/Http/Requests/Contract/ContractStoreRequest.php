<?php

namespace App\Http\Requests\Contract;

use App\Models\Application;
use App\Models\Currency;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class ContractStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $transactionType = $this->input('transaction_type');
        $isIncome = $transactionType == 2; // TYPE_INCOME

        return [
            'contract_number' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'project_id' => 'required|integer',
            'application_id' => [
                $isIncome ? 'nullable' : 'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!$value) return; // Skip if nullable and empty

                    $application = Application::find($value);
                    $currencyId = $this->input('currency_id');

                    if ($application && $application->currency_id !== (int) $currencyId) {
                        $fail(__('app.label.application_currency_mismatch'));
                    }
                }
            ],
            'budget_sum' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currency,id',
            'deadline' => 'required|date',
            'application_type' => 'required|integer',
            'transaction_type' => 'required|integer|in:1,2',
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:51200'],
            'recipients' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $transactionType = $this->input('transaction_type');
                    $isIncome = $transactionType == 2; // TYPE_INCOME

                    if (!is_array($value) || count($value) < 1) {
                        $fail('Необходимо выбрать минимум 1 получателя.');
                    }

                    $currencyId = $this->input('currency_id');
                    $currency = Currency::find($currencyId);

                    // For income type, only require Legal department (7)
                    // For expense type, require both Legal (7) and Financial (8)
                    $requiredDepartments = $isIncome ? [7] : [7, 8];
                    $usersByDepartment = User::whereIn('department_id', $requiredDepartments)->whereIn('id', $value)->pluck('department_id')->toArray();

                    foreach ($requiredDepartments as $departmentId) {
                        $departmentName = Department::find($departmentId)->name ?? trans('app.label.unknown_department');
                        if (!in_array($departmentId, $usersByDepartment)) {
                            $fail(trans('app.label.select_recipient_from_department', ['department' => $departmentName]));
                        }
                    }

                    // For income type, accounting department (9) is not required
                    // For expense type, accounting is required if currency is not UZS
                    if (!$isIncome && $currency && strtoupper($currency->short_name) !== 'UZS') {
                        $hasAccountant = User::whereIn('id', $value)
                            ->where('department_id', 9)
                            ->exists();

                        if (!$hasAccountant) {
                            $fail(trans('app.label.select_accounting_recipient'));
                        }
                    }
                }
            ],
        ];
    }


    public function messages()
    {
        return [
            'recipients.required' => trans('app.label.recipients_required'),
            'files.required' => trans('app.label.files_required'),
            'files.min' => trans('app.label.files_min'),
            'files.*.mimes' => trans('app.label.files_invalid_type'),
            'files.*.max' => trans('app.label.files_max_size'),
            'budget_sum.min' => trans('app.label.budget_nonzero'),
        ];
    }
}
