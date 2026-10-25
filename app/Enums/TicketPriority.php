<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Basse',
            self::Medium => 'Moyenne',
            self::High => 'Haute',
            self::Urgent => 'Urgente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
            self::Medium => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            self::High => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
            self::Urgent => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
        };
    }
}
