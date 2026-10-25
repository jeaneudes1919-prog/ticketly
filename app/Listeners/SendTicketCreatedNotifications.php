<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Services\TicketService;

class SendTicketCreatedNotifications
{
    public function __construct(private TicketService $service) {}

    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        $this->service->notify(
            $this->service->staffFor($ticket),
            $ticket,
            'Nouveau ticket '.$ticket->reference,
            $ticket->subject,
        );
    }
}
