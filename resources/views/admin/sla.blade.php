@extends('layouts.app')
@section('title', 'Politiques SLA')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Administration</p>
        <h1 class="mt-1 font-display text-3xl font-bold">Politiques SLA</h1>
        <p class="text-slate-500">Les délais sont exprimés en minutes et s’appliquent aux nouveaux tickets.</p>
    </div>
    <a href="{{ route('admin.canned') }}" class="btn-ghost"><x-icon n="message" class="h-4 w-4"/>Réponses prédéfinies</a>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
    <form method="POST" action="{{ route('admin.sla.save') }}" class="card p-5 sm:p-6">
        @csrf
        <div class="space-y-4">
            @foreach ($policies as $policy)
                @php $priority = \App\Enums\TicketPriority::from($policy->priority); @endphp
                <div class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="badge {{ $priority->color() }}">{{ $priority->label() }}</span>
                        <span class="text-xs text-slate-400">{{ $priority->value }}</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div><label class="label" for="first-{{ $policy->priority }}">1re réponse (min)</label><input id="first-{{ $policy->priority }}" type="number" min="1" name="policies[{{ $policy->priority }}][first_response_minutes]" value="{{ $policy->first_response_minutes }}" class="input"></div>
                        <div><label class="label" for="resolution-{{ $policy->priority }}">Résolution (min)</label><input id="resolution-{{ $policy->priority }}" type="number" min="1" name="policies[{{ $policy->priority }}][resolution_minutes]" value="{{ $policy->resolution_minutes }}" class="input"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="btn-primary mt-5"><x-icon n="check" class="h-4 w-4"/>Enregistrer les SLA</button>
    </form>
    <aside class="card h-fit p-5">
        <h2 class="font-display text-lg font-bold">Bon à savoir</h2>
        <ul class="mt-3 space-y-3 text-sm text-slate-600 dark:text-slate-400">
            <li class="flex gap-2"><x-icon n="clock" class="mt-0.5 h-4 w-4 shrink-0 text-blue-500"/>La commande <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">tickets:check-sla</code> tourne chaque minute.</li>
            <li class="flex gap-2"><x-icon n="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500"/>Une alerte est envoyée 15 minutes avant l’échéance.</li>
            <li class="flex gap-2"><x-icon n="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500"/>La première réponse publique d’un agent est horodatée automatiquement.</li>
        </ul>
    </aside>
</div>
@endsection
