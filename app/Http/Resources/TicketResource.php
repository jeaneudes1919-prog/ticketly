<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $staff = $request->user()?->isStaff();

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => ['value' => $this->status->value, 'label' => $this->status->label()],
            'priority' => ['value' => $this->priority->value, 'label' => $this->priority->label()],
            'category' => $this->category?->name,
            'customer' => ['id' => $this->customer_id, 'name' => $this->customer?->name],
            'assignee' => $this->assignee ? ['id' => $this->assignee->id, 'name' => $this->assignee->name] : null,
            'due_at' => $this->due_at?->toIso8601String(),
            'first_response_at' => $this->first_response_at?->toIso8601String(),
            'sla_breached' => $this->sla_breached,
            'created_at' => $this->created_at->toIso8601String(),
            'replies' => $this->whenLoaded('replies', fn () => $this->replies
                ->when(! $staff, fn ($c) => $c->where('is_internal', false))
                ->map(fn ($r) => ['id' => $r->id, 'author' => $r->user->name, 'body' => $r->body, 'is_internal' => $r->is_internal, 'created_at' => $r->created_at->toIso8601String()])->values()),
        ];
    }
}
