<?php

namespace App\Http\Requests\Application;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationStoreRequest extends FormRequest
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
            'recipients' => [
                'required_if:type,' . Application::TYPE_REQUEST,
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') == Application::TYPE_REQUEST && (!is_array($value) || count($value) < 2)) {
                        $fail(__('app.label.min_recipients_required'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'recipients.required_if' => 'Для заявок типа "Запрос" необходимо указать минимум 2 получателя.',
        ];
    }



}
