<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'serial_number' => ['required', 'string', 'max:255'],
            'inventory_number' => ['nullable', 'string', 'max:255'],
            'parameters' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'brand_id' => ['required', 'exists:product_brands,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'sort' => ['nullable', 'integer'],
            'status' => ['boolean'],
        ];
    }

}
