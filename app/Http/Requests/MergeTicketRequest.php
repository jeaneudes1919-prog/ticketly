<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStaff()
            && $this->user()->can('manage', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'target_id' => [
                'required',
                'integer',
                Rule::exists('tickets', 'id')->where(fn ($query) => $query->where('id', '!=', $this->route('ticket')->id)),
            ],
        ];
    }

    public function target(): Ticket
    {
        return Ticket::findOrFail($this->validated()['target_id']);
    }
}
