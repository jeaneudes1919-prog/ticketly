<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tickets:check-sla')->everyMinute();
Schedule::command('tickets:auto-close')->daily();
