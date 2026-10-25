@extends('layouts.app')
@section('title', 'Mes tickets')
@section('content')
<div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div><h1 class="font-display text-2xl font-bold sm:text-3xl">Mes tickets</h1><p class="text-slate-500">Bonjour {{ auth()->user()->name }}, voici l'état de vos demandes.</p></div>
    <a href="{{ route('portal.create') }}" class="btn-primary w-full sm:w-auto"><x-icon n="plus" class="h-4 w-4"/>Nouveau ticket</a>
</div>
<div class="mt-5 grid grid-cols-2 gap-3 sm:mt-6 sm:gap-4 lg:grid-cols-4">
    <x-stat label="Total" :value="$stats['total']" :i="0"/><x-stat label="Ouverts" :value="$stats['open']" tone="violet" :i="1"/><x-stat label="En attente de vous" :value="$stats['pending']" tone="amber" :i="2"/><x-stat label="Résolus" :value="$stats['resolved']" tone="green" :i="3"/>
</div>
<form method="GET" class="mt-5 grid grid-cols-1 gap-2 sm:mt-6 sm:flex sm:flex-wrap sm:gap-3">
    <input name="q" value="{{ request('q') }}" class="input sm:max-w-xs" placeholder="Référence ou sujet">
    <div class="flex gap-2">
        <select name="status" class="input min-w-0 flex-1 sm:max-w-[200px]" onchange="this.form.submit()"><option value="">Tous les statuts</option>@foreach (\App\Enums\TicketStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach</select>
        <button class="btn-ghost shrink-0">Filtrer</button>
    </div>
</form>
<div class="mt-4 space-y-2.5 sm:mt-5 sm:space-y-3">
    @forelse ($tickets as $i => $t)
        <a href="{{ route('portal.show', $t) }}" class="card fade-up flex flex-col gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-lg sm:flex-row sm:items-center sm:justify-between sm:p-5" style="--i:{{ $i }}">
            <div class="min-w-0"><p class="text-xs font-semibold text-blue-600">{{ $t->reference }} · {{ $t->category?->name ?? 'Général' }}</p><p class="mt-0.5 truncate font-semibold">{{ $t->subject }}</p><p class="text-xs text-slate-500">Créé {{ $t->created_at->diffForHumans() }}@if ($t->assignee) · Pris en charge par {{ $t->assignee->name }}@endif</p></div>
            <div class="flex flex-wrap items-center gap-2"><span class="badge {{ $t->priority->color() }}">{{ $t->priority->label() }}</span><span class="badge {{ $t->status->color() }}">{{ $t->status->label() }}</span></div>
        </a>
    @empty
        <div class="card px-4 py-12 text-center sm:px-6 sm:py-16">
            <x-empty-state icon="ticket" title="Aucun ticket" text="Une question ou un problème ? Notre équipe est là pour vous aider.">
                <a href="{{ route('portal.create') }}" class="btn-primary">Ouvrir un ticket</a>
            </x-empty-state>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $tickets->links('components.pagination') }}</div>
@endsection
