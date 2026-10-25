@props(['t'])
@php
    $late = ! $t->isFinal() && $t->due_at && now()->gt($t->due_at);
    $soon = ! $t->isFinal() && $t->due_at && ! $late && now()->diffInMinutes($t->due_at) < 60;
@endphp
@if ($t->isFinal())
    <span class="text-xs {{ $t->sla_breached ? 'text-red-500' : 'text-emerald-600' }}">{{ $t->sla_breached ? 'SLA dépassé' : 'SLA respecté' }}</span>
@elseif (! $t->due_at)
    <span class="text-xs text-slate-400">—</span>
@elseif ($late || $t->sla_breached)
    <span class="badge animate-pulse bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300">En retard de {{ $t->due_at->diffForHumans(null, true) }}</span>
@else
    <span class="badge {{ $soon ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' }}">Reste {{ $t->due_at->diffForHumans(null, true) }}</span>
@endif
