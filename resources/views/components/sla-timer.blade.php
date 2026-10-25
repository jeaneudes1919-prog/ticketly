@props(['t'])
@php
    $late = ! $t->isFinal() && $t->due_at && now()->gt($t->due_at);
    $due = $t->due_at?->valueOf();
@endphp
@if ($t->isFinal())
    <span class="badge {{ $t->sla_breached ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' }}">
        <x-icon n="shield" class="h-3.5 w-3.5"/>{{ $t->sla_breached ? 'SLA dépassé' : 'SLA respecté' }}
    </span>
@elseif (! $due)
    <span class="badge bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">SLA non défini</span>
@else
    <span x-data="{
              end: {{ $due }},
              now: Date.now(),
              get remaining() { return Math.max(0, this.end - this.now); },
              get hours() { return Math.floor(this.remaining / 3600000); },
              get minutes() { return Math.floor((this.remaining % 3600000) / 60000); },
              get seconds() { return Math.floor((this.remaining % 60000) / 1000); },
              get label() {
                  if (this.remaining <= 0) return 'Dépassé';
                  return (this.hours > 0 ? this.hours + ' h ' : '') + String(this.minutes).padStart(2, '0') + ' min ' + String(this.seconds).padStart(2, '0') + ' s';
              }
          }"
          x-init="setInterval(() => now = Date.now(), 1000)"
          class="badge tabular-nums"
          :class="remaining <= 0 || {{ $late ? 'true' : 'false' }} ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' : (remaining < 900000 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300')">
        <x-icon n="clock" class="h-3.5 w-3.5"/><span x-text="label">…</span>
    </span>
@endif
