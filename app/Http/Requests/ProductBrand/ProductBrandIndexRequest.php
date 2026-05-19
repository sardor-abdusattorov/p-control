<?php

namespace App\Http\Requests\ProductBrand;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrandIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'field' => ['in:title'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
