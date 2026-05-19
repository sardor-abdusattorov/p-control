<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryIndexRequest extends FormRequest
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
