<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Services\TicketService;

class SendTicketAssignedNotifications
{
    public function __construct(private TicketService $service) {}

    public function handle(TicketAssigned $event): void
    {
        $this->service->notify(
            [$event->assignee],
            $event->ticket,
            'Ticket assigné : '.$event->ticket->reference,
            $event->ticket->subject,
        );
    }
}
