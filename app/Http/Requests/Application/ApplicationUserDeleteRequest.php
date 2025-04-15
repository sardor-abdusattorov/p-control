<?php

namespace App\Http\Requests\Application;

use App\Models\Application;
use App\Models\Approvals;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationUserDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $application = $this->route('application');

                if (!$application instanceof Application) {
                    return $fail(__('app.label.invalid_application'));
                }

                $user = User::find($value);
                if (!$user) {
                    return $fail(__('app.label.not_found', [
                        'name' => __('app.label.unknown_user')
                    ]));
                }

                $approval = Approvals::valid()
                    ->where('approvable_type', Application::class)
                    ->where('approvable_id', $application->id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$approval) {
                    return $fail(__('app.label.not_found', [
                        'name' => __('app.label.approver')
                    ]));
                }

                // Запретить удаление, если пользователь из отдела 7 или 8, или 9 если валюта не 1
                $protectedDepartments = [7, 8];
                if (in_array($user->department_id, $protectedDepartments)) {
                    return $fail(__('app.label.cannot_delete_protected_department', [
                        'name' => $user->name ?? __('app.label.unknown_user')
                    ]));
                }

                if ($application->currency_id != 1 && $user->department_id === 9) {
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
