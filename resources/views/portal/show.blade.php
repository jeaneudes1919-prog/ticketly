@extends('layouts.app')
@section('title', $ticket->reference)
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('portal') }}" class="text-sm text-slate-500 hover:text-blue-600">← Mes tickets</a>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div><p class="text-sm font-semibold text-blue-600">{{ $ticket->reference }}</p><h1 class="font-display text-2xl font-bold sm:text-3xl">{{ $ticket->subject }}</h1></div>
        <div class="flex flex-wrap items-center gap-2"><span class="badge {{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</span><span class="badge {{ $ticket->status->color() }}">{{ $ticket->status->label() }}</span><x-sla-timer :t="$ticket"/></div>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-slate-500"><span>Créé le {{ $ticket->created_at->translatedFormat('d M Y') }}</span>@if ($ticket->category)<span>{{ $ticket->category->name }}</span>@endif<span>{{ $ticket->assignee ? 'Pris en charge par '.$ticket->assignee->name : 'En attente de prise en charge' }}</span></div>
    @if ($ticket->sla_breached && ! $ticket->isFinal())
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            <x-icon n="alert" class="mt-0.5 h-5 w-5 shrink-0"/><div><b>Nous avons pris du retard.</b> Notre équipe a été relancée et met tout en œuvre pour vous répondre rapidement.</div>
        </div>
    @endif
    <div class="mt-6">@include('partials.thread', ['ticket' => $ticket->setRelation('customer', $ticket->customer ?? auth()->user())])</div>
    @if ($ticket->status !== \App\Enums\TicketStatus::Closed)
        <form method="POST" action="{{ route('portal.reply', $ticket) }}" enctype="multipart/form-data" class="card mt-6 space-y-4 p-5">
            @csrf
            <label class="label" for="body">Votre réponse</label>
            <textarea id="body" name="body" rows="4" class="input" placeholder="Écrivez votre message…" required>{{ old('body') }}</textarea>@error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            @include('partials.dropzone')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <button class="btn-primary"><x-icon n="send" class="h-4 w-4"/>Envoyer</button>
            </div>
        </form>
    @endif
    <div class="mt-4 flex flex-wrap gap-3">
        @if ($ticket->status !== \App\Enums\TicketStatus::Closed)
            <form method="POST" action="{{ route('portal.close', $ticket) }}" @submit.prevent="$store.confirm.ask($el, 'Fermer ce ticket ?', 'Vous pourrez le rouvrir pendant 14 jours si le problème revient.', false, 'Fermer le ticket')">@csrf<button class="btn-ghost">Fermer le ticket</button></form>
        @endif
        @if ($ticket->isFinal())
            <form method="POST" action="{{ route('portal.reopen', $ticket) }}">@csrf<button class="btn-ghost">Rouvrir le ticket</button></form>
            <p class="text-xs text-slate-400">Réouverture possible pendant 14 jours après la résolution.</p>
        @endif
    </div>
    @if ($ticket->isFinal())
        <div class="card mt-6 p-5" x-data="{ s: {{ $ticket->satisfaction ?? 0 }}, hover: 0 }">
            <h2 class="font-display text-lg font-bold">Comment s'est passé le traitement ?</h2>
            @if ($ticket->satisfaction)
                <p class="mt-1 text-sm text-slate-500">Merci ! Vous avez donné {{ $ticket->satisfaction }}/5.</p>
            @else
                <form method="POST" action="{{ route('portal.rate', $ticket) }}" class="mt-3 space-y-3">@csrf<input type="hidden" name="score" :value="s">
                    <div class="flex gap-1">@for ($i = 1; $i <= 5; $i++)<button type="button" @click="s = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0" class="text-3xl transition hover:scale-125" :class="(hover || s) >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'" aria-label="{{ $i }} étoile(s)">★</button>@endfor</div>
                    <textarea name="comment" rows="2" class="input" placeholder="Un commentaire ? (facultatif)"></textarea>
                    <button class="btn-primary" :disabled="! s">Envoyer mon évaluation</button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
