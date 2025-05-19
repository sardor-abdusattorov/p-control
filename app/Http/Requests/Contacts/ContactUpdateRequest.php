<?php

namespace App\Http\Requests\Contacts;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['nullable', 'string', 'max:255'],
            'prefix'          => ['nullable', 'string', 'max:255'],
            'firstname'      => ['required', 'string', 'max:255'],
            'lastname'       => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20'],
            'cellphone'      => ['required', 'string', 'max:20'],
            'email'          => ['required', 'email', 'max:255'],
            'company'        => ['required', 'string', 'max:255'],
            'language'       => ['nullable', 'string', 'max:50'],
            'country'        => ['required'],
            'city'           => ['required'],
            'post_box'       => ['nullable', 'string', 'max:50'],
            'zip_code'       => ['nullable', 'string', 'max:20'],
            'address'        => ['required', 'string'],
            'address2'       => ['nullable', 'string'],
            'category_id'    => ['required', 'exists:contact_categories,id'],
            'subcategory_id' => ['required', 'exists:contact_subcategories,id'],
            'status'         => ['required', 'integer'],
        ];
    }
}
