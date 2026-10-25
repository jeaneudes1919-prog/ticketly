<?php

namespace App\Listeners;

use App\Events\TicketReplied;
use App\Services\TicketService;

class SendTicketRepliedNotifications
{
    public function __construct(private TicketService $service) {}

    public function handle(TicketReplied $event): void
    {
        $ticket = $event->ticket;
        $actor = $event->actor;
        $excerpt = (string) str($event->reply->body)->limit(80);

        if ($event->internal) {
            $targets = $ticket->assignee && $ticket->assignee_id !== $actor->id ? [$ticket->assignee] : [];
            $this->service->notify($targets, $ticket, 'Note interne : '.$ticket->reference, $excerpt);

            return;
        }

        if ($actor->isStaff()) {
            $this->service->notify([$ticket->customer], $ticket, 'Réponse à votre ticket '.$ticket->reference, $excerpt);

            return;
        }

        $targets = $ticket->assignee ? [$ticket->assignee] : $this->service->staffFor($ticket);
        $this->service->notify($targets, $ticket, 'Nouvelle réponse client : '.$ticket->reference, $excerpt);
    }
}
