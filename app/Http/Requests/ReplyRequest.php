<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,txt,docx,zip'],
        ];
    }
}
