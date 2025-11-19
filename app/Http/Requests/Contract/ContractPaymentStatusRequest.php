<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ContractPaymentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_status' => 'required|integer|in:0,1,2',
        ];
    }

    public function messages()
    {
        return [
            'payment_status.required' => trans('app.label.payment_status_required'),
            'payment_status.in' => trans('app.label.payment_status_invalid'),
        ];
    }
}
