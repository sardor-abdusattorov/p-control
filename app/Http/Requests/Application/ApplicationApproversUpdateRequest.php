<?php

namespace App\Http\Requests\Application;

use App\Models\Application;
use App\Models\User;
use App\Models\Department;
use App\Models\Approvals;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationApproversUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $application = $this->route('application');

                    if (!$application instanceof Application) {
                        $fail(__('app.label.invalid_application'));
                        return;
                    }

                    $currencyId = $application->currency_id;
                    $requiredDepartments = [7, 8];
                    $departmentsFound = User::whereIn('id', $value)
                        ->whereIn('department_id', $requiredDepartments)
                        ->pluck('department_id')
                        ->toArray();

                    foreach ($requiredDepartments as $departmentId) {
                        if (!in_array($departmentId, $departmentsFound)) {
                            $name = Department::find($departmentId)->name ?? trans('app.label.unknown_department');
                            $fail(trans('app.label.select_recipient_from_department', ['department' => $name]));
                        }
                    }
                    if ($currencyId != 1) {
                        $hasAccountant = User::whereIn('id', $value)
                            ->where('department_id', 9)
                            ->exists();

                        if (!$hasAccountant) {
                            $fail(trans('app.label.select_accounting_recipient'));
                        }
                    }
                    $currentApprovals = $application->approvals()
                        ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
                        ->get();

                    $removableIds = $currentApprovals->pluck('user_id')->diff($value);

                    foreach ($currentApprovals as $approval) {
                        if (
                            $removableIds->contains($approval->user_id) &&
                            in_array($approval->approved, [
                                Approvals::STATUS_APPROVED,
                                Approvals::STATUS_REJECTED,
                            ])
                        ) {
                            $fail(trans('app.label.cannot_delete_approved_list_single', [
                                'name' => $approval->user?->name ?? trans('app.label.unknown_user'),
                            ]));
                        }
                    }
                },
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => trans('app.label.recipients_required'),
            'user_ids.array' => trans('app.label.recipients_must_be_array'),
        ];
    }
}
