<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractUpdateRequest extends FormRequest
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

            'recipients' => ['required', 'array'],
            'recipients.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages()
    {
        return [
            'recipients.required' => trans('app.label.recipients_required'),
            'files.*.mimes' => trans('app.label.files_invalid_type'),
            'files.*.max' => trans('app.label.files_max_size'),
            'files.required' => trans('app.label.files_required'),
        ];
    }
}
