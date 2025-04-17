<?php

namespace App\Http\Requests\Contract;

use App\Models\Approvals;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ContractUserDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $contract = $this->route('contract');

                if (!$contract instanceof Contract) {
                    return $fail(__('app.label.invalid_contract'));
                }

                $user = User::find($value);
                if (!$user) {
                    return $fail(__('app.label.not_found', [
                        'name' => __('app.label.unknown_user')
                    ]));
                }

                $approval = Approvals::valid()
                    ->where('approvable_type', Contract::class)
                    ->where('approvable_id', $contract->id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$approval) {
                    return $fail(__('app.label.not_found', [
                        'name' => __('app.label.approver')
                    ]));
                }

                $protectedDepartments = [7, 8];
                if (in_array($user->department_id, $protectedDepartments)) {
                    return $fail(__('app.label.cannot_delete_protected_department', [
                        'name' => $user->name ?? __('app.label.unknown_user')
                    ]));
                }

                if ($contract->currency_id != 1 && $user->department_id === 9) {
                    return $fail(__('app.label.cannot_delete_protected_accountant', [
                        'name' => $user->name ?? __('app.label.unknown_user')
                    ]));
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('app.label.recipients_required'),
            'user_id.integer' => __('app.label.recipients_must_be_array'),
        ];
    }
}
