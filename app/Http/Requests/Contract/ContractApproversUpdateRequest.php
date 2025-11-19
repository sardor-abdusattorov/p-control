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

                    // Проверка минимального количества получателей
                    $isIncome = $contract->transaction_type == Contract::TYPE_INCOME;
                    $minRecipients = $isIncome ? 1 : 2;

                    if (count($value) < $minRecipients) {
                        $fail($isIncome
                            ? 'Необходимо выбрать минимум 1 получателя.'
                            : 'Необходимо выбрать минимум 2 получателя.');
                        return;
                    }

                    // Для прихода не проверяем обязательные отделы и бухгалтерию
                    if ($isIncome) {
                        // Переходим к проверке удаления подтвержденных получателей
                        goto check_approved;
                    }

                    $currencyId = $contract->currency_id;
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

                    check_approved:

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
