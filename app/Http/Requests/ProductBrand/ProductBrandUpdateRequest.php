<?php

namespace App\Http\Requests\ProductBrand;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrandUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
