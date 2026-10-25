@extends('layouts.app')
@section('title', 'Mes tickets')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="font-display text-3xl font-bold">Mes tickets</h1><p class="text-slate-500">Bonjour {{ auth()->user()->name }}, voici l'état de vos demandes.</p></div>
    <a href="{{ route('portal.create') }}" class="btn-primary"><x-icon n="plus" class="h-4 w-4"/>Nouveau ticket</a>
</div>
<div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-stat label="Total" :value="$stats['total']" :i="0"/><x-stat label="Ouverts" :value="$stats['open']" tone="violet" :i="1"/><x-stat label="En attente de vous" :value="$stats['pending']" tone="amber" :i="2"/><x-stat label="Résolus" :value="$stats['resolved']" tone="green" :i="3"/>
</div>
<form method="GET" class="mt-8 flex flex-wrap gap-3">
    <input name="q" value="{{ request('q') }}" class="input max-w-xs" placeholder="Référence ou sujet">
    <select name="status" class="input max-w-[200px]" onchange="this.form.submit()"><option value="">Tous les statuts</option>@foreach (\App\Enums\TicketStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach</select>
    <button class="btn-ghost">Filtrer</button>
</form>
<div class="mt-5 space-y-3">
    @forelse ($tickets as $i => $t)
        <a href="{{ route('portal.show', $t) }}" class="card fade-up flex flex-wrap items-center justify-between gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5" style="--i:{{ $i }}">
            <div class="min-w-0"><p class="text-xs font-semibold text-blue-600">{{ $t->reference }} · {{ $t->category?->name ?? 'Général' }}</p><p class="mt-0.5 truncate font-semibold">{{ $t->subject }}</p><p class="text-xs text-slate-500">Créé {{ $t->created_at->diffForHumans() }}@if ($t->assignee) · Pris en charge par {{ $t->assignee->name }}@endif</p></div>
            <div class="flex items-center gap-2"><span class="badge {{ $t->priority->color() }}">{{ $t->priority->label() }}</span><span class="badge {{ $t->status->color() }}">{{ $t->status->label() }}</span></div>
        </a>
    @empty
        <div class="card px-6 py-16 text-center">
            <x-empty-state icon="ticket" title="Aucun ticket" text="Une question ou un problème ? Notre équipe est là pour vous aider.">
                <a href="{{ route('portal.create') }}" class="btn-primary">Ouvrir un ticket</a>
            </x-empty-state>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $tickets->links('components.pagination') }}</div>
@endsection
