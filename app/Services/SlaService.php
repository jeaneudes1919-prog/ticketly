<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use Illuminate\Support\Carbon;

class SlaService
{
    public function policy(TicketPriority $priority): ?SlaPolicy
    {
        return SlaPolicy::where('priority', $priority->value)->first();
    }

    public function resolutionDue(Ticket $ticket): ?Carbon
    {
        $policy = $this->policy($ticket->priority);

        return $policy ? $ticket->created_at->copy()->addMinutes($policy->resolution_minutes) : null;
    }

    public function firstResponseDue(Ticket $ticket): ?Carbon
    {
        $policy = $this->policy($ticket->priority);

        return $policy ? $ticket->created_at->copy()->addMinutes($policy->first_response_minutes) : null;
    }

    public function isLate(Ticket $ticket): bool
    {
        if ($ticket->isFinal()) {
            return false;
        }
        if ($ticket->due_at && now()->gt($ticket->due_at)) {
            return true;
        }
        $firstResponseDue = $this->firstResponseDue($ticket);

        return ! $ticket->first_response_at && $firstResponseDue && now()->gt($firstResponseDue);
    }

    /** Marque les tickets ouverts dont le SLA est dépassé et prévient les responsables. */
    public function checkBreaches(): int
    {
        $count = 0;
        $tickets = Ticket::open()->where('sla_breached', false)->with('assignee')->get();

        foreach ($tickets as $ticket) {
            if ($this->isLate($ticket)) {
                $ticket->update(['sla_breached' => true]);
                $service = app(TicketService::class);
                $service->log($ticket, null, 'sla_breached');
                $service->notify(
                    $ticket->assignee ? [$ticket->assignee] : $service->staffFor($ticket),
                    $ticket,
                    'SLA dépassé : '.$ticket->reference,
                    $ticket->subject,
                );
                $count++;

                continue;
            }

            if ($ticket->sla_warned_at) {
                continue;
            }

            $resolutionSoon = $ticket->due_at
                && $ticket->due_at->gt(now())
                && $ticket->due_at->lte(now()->addMinutes(15));
            $firstResponseDue = $this->firstResponseDue($ticket);
            $firstResponseSoon = ! $ticket->first_response_at
                && $firstResponseDue
                && $firstResponseDue->gt(now())
                && $firstResponseDue->lte(now()->addMinutes(15));

            if ($resolutionSoon || $firstResponseSoon) {
                $ticket->update(['sla_warned_at' => now()]);
                $service = app(TicketService::class);
                $service->log($ticket, null, 'sla_warning');
                $service->notify(
                    $ticket->assignee ? [$ticket->assignee] : $service->staffFor($ticket),
                    $ticket,
                    'SLA bientôt dépassé : '.$ticket->reference,
                    'Il reste moins de 15 minutes avant l’échéance SLA de ce ticket.',
                );
            }
        }

        return $count;
    }
}
