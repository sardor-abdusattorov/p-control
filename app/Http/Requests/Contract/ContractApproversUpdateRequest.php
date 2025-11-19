<?php

namespace App\Http\Requests\Contract;

use App\Models\Contract;
use App\Models\User;
use App\Models\Department;
use App\Models\Approvals;
use Illuminate\Foundation\Http\FormRequest;

class ContractApproversUpdateRequest extends FormRequest
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
                    $contract = $this->route('contract');

                    if (!$contract instanceof Contract) {
                        $fail(__('app.label.invalid_contract'));
                        return;
                    }

                    $currencyId = $contract->currency_id;
                    $isIncome = $contract->transaction_type == 2; // TYPE_INCOME

                    // For income type, only require Legal department (7)
                    // For expense type, require both Legal (7) and Financial (8)
                    $requiredDepartments = $isIncome ? [7] : [7, 8];
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

                    // For income type, accounting department (9) is not required
                    // For expense type, accounting is required if currency is not UZS (currency_id != 1)
                    if (!$isIncome && $currencyId != 1) {
                        $hasAccountant = User::whereIn('id', $value)
                            ->where('department_id', 9)
                            ->exists();

                        if (!$hasAccountant) {
                            $fail(trans('app.label.select_accounting_recipient'));
                        }
                    }

                    $currentApprovals = $contract->approvals()
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
