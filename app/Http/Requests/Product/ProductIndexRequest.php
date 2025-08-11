<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Или добавьте логику проверки прав доступа, если необходимо
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'field' => ['in:title,inventory_number,category_id,user_id,status'],
            'order' => ['nullable', 'in:asc,desc'],
            'perPage' => ['nullable', 'numeric'],
            'title' => ['nullable', 'string'],
            'inventory_number' => ['sometimes', 'nullable', 'string'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:product_categories,id'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }

}
