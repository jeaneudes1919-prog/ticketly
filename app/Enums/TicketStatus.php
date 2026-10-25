<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Ouvert',
            self::Pending => 'En attente du client',
            self::InProgress => 'En cours',
            self::Resolved => 'Résolu',
            self::Closed => 'Fermé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            self::Pending => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            self::InProgress => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
            self::Resolved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            self::Closed => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Resolved, self::Closed], true);
    }
}
