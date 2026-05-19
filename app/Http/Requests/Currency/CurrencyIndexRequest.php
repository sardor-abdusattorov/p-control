<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'field' => ['in:name,short_name,value,status,created_at,'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
