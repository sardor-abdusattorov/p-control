<?php

namespace App\Http\Requests\Status;

use Illuminate\Foundation\Http\FormRequest;

class StatusStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
