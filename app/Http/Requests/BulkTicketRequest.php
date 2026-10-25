<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStaff();
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tickets,id'],
            'action' => ['required', 'in:assign,status,close'],
            'value' => ['nullable', 'string', 'max:60'],
        ];
    }
}
