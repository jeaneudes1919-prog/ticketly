<?php

namespace App\Listeners;

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Services\TicketService;

class SendTicketStatusChangedNotifications
{
    public function __construct(private TicketService $service) {}

    public function handle(TicketStatusChanged $event): void
    {
        $ticket = $event->ticket;

        if ($event->to === TicketStatus::Resolved) {
            $this->service->notify(
                [$ticket->customer],
                $ticket,
                'Votre ticket '.$ticket->reference.' est résolu',
                'Vous pouvez maintenant noter la qualité du traitement depuis votre espace client.',
            );

            return;
        }

        if ($event->from->isFinal() && $event->to === TicketStatus::Open) {
            $targets = $ticket->assignee ? [$ticket->assignee] : $this->service->staffFor($ticket);
            $this->service->notify($targets, $ticket, 'Ticket rouvert : '.$ticket->reference, $ticket->subject);
        }
    }
}
