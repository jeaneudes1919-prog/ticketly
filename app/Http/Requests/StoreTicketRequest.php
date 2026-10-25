<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,txt,docx,zip'],
        ];
    }
}
