<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Console\Command;

class AutoClose extends Command
{
    protected $signature = 'tickets:auto-close';

    protected $description = 'Ferme les tickets résolus depuis plus de 3 jours';

    public function handle(TicketService $service): int
    {
        $n = 0;
        foreach (Ticket::where('status', TicketStatus::Resolved->value)->where('resolved_at', '<=', now()->subDays(3))->get() as $t) {
            $t->status = TicketStatus::Closed;
            $service->stampStatus($t);
            $t->save();
            $service->log($t, null, 'auto_closed');
            $n++;
        }
        $this->info("$n ticket(s) fermé(s) automatiquement.");

        return self::SUCCESS;
    }
}
