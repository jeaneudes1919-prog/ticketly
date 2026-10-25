<?php

namespace App\Console\Commands;

use App\Services\SlaService;
use Illuminate\Console\Command;

class CheckSla extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Marque les tickets dont le SLA est dépassé et notifie les responsables';

    public function handle(SlaService $sla): int
    {
        $n = $sla->checkBreaches();
        $this->info("$n ticket(s) en dépassement de SLA.");

        return self::SUCCESS;
    }
}
