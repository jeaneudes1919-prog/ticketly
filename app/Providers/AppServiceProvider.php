<?php

namespace App\Providers;

use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Events\TicketStatusChanged;
use App\Listeners\SendTicketAssignedNotifications;
use App\Listeners\SendTicketCreatedNotifications;
use App\Listeners\SendTicketRepliedNotifications;
use App\Listeners\SendTicketStatusChangedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(TicketCreated::class, SendTicketCreatedNotifications::class);
        Event::listen(TicketReplied::class, SendTicketRepliedNotifications::class);
        Event::listen(TicketAssigned::class, SendTicketAssignedNotifications::class);
        Event::listen(TicketStatusChanged::class, SendTicketStatusChangedNotifications::class);
    }
}
