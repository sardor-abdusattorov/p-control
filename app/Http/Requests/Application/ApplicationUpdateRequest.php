<?php

namespace App\Http\Requests\Application;

use App\Models\Currency;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'integer'],
            'type' => ['required', 'integer', 'in:1,2'],
            'currency_id' => ['required', 'exists:currency,id'],
            'files' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $hasOld = $this->input('old_files') && is_array($this->input('old_files')) && count($this->input('old_files')) > 0;
                    $hasNew = $this->hasFile('files');

                    if (!$hasOld && !$hasNew) {
                        $fail(trans('app.label.files_required'));
                    }
                },
            ],
            'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:51200'],
            'deleted_old_file_ids' => ['nullable', 'array'],
            'deleted_old_file_ids.*' => ['integer'],
            'recipients' => [
                $this->input('type') != 2 ? 'required' : 'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') == 2) return;

                    $currencyId = $this->input('currency_id');
                    $currency = Currency::find($currencyId);

                    $departments = [7, 8];
                    $usersByDepartment = User::whereIn('department_id', $departments)
                        ->whereIn('id', $value)
                        ->pluck('department_id')
                        ->toArray();

                    foreach ($departments as $departmentId) {
                        $departmentName = Department::find($departmentId)->name ?? trans('app.label.unknown_department');
                        if (!in_array($departmentId, $usersByDepartment)) {
                            $fail(trans('app.label.select_recipient_from_department', ['department' => $departmentName]));
                        }
                    }

                    if ($currency && $currency->id !== 1) {
                        $hasAccountant = User::whereIn('id', $value)
                            ->where('department_id', 9)
                            ->exists();

                        if (!$hasAccountant) {
                            $fail(trans('app.label.select_accounting_recipient'));
                        }
                    }
                },
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
        ];
    }
}
