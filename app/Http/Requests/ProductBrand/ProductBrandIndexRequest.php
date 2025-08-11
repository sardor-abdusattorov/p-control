<?php

namespace App\Http\Requests\ProductBrand;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrandIndexRequest extends FormRequest
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
            'field' => ['in:title'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
