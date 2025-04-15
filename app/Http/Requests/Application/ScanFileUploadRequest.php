<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class ScanFileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:pdf', 'max:30720'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => __('app.label.files_required'),
            'files.array' => __('app.label.files_array'),
            'files.*.required' => __('app.label.each_file_required'),
            'files.*.file' => __('app.label.each_file_must_be_file'),
            'files.*.mimes' => __('app.label.only_pdf_allowed'),
            'files.*.max' => __('app.label.file_too_large', ['size' => '30MB']),
        ];
    }
}
