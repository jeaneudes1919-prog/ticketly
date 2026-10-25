<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Agent = 'agent';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Agent => 'Agent',
            self::Customer => 'Client',
        };
    }
}
