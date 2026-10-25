<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $u, Ticket $t): bool
    {
        return match ($u->role) {
            Role::Admin => true,
            Role::Customer => $t->customer_id === $u->id,
            Role::Agent => Ticket::visibleTo($u)->whereKey($t->id)->exists(),
        };
    }

    public function manage(User $u, Ticket $t): bool
    {
        return $u->isStaff() && $this->view($u, $t);
    }

    public function customerAct(User $u, Ticket $t): bool
    {
        return $u->role === Role::Customer && $t->customer_id === $u->id;
    }
}
