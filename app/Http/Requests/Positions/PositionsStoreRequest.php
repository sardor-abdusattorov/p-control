<?php

namespace App\Http\Requests\Positions;

use Illuminate\Foundation\Http\FormRequest;

class PositionsStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'departments' => 'required|array|min:1',
        ];
    }
}
