@extends('layouts.app')
@section('title', 'Réponses prédéfinies')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Administration</p>
        <h1 class="mt-1 font-display text-3xl font-bold">Réponses prédéfinies</h1>
        <p class="text-slate-500">Des modèles réutilisables par les agents, avec un raccourci de saisie.</p>
    </div>
    <a href="{{ route('admin.sla') }}" class="btn-ghost"><x-icon n="clock" class="h-4 w-4"/>Politiques SLA</a>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_400px]">
    <div class="card overflow-hidden">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($canned as $response)
                <li class="p-4" x-data="{ edit: false }">
                    <div class="flex items-start gap-3" x-show="! edit">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><x-icon n="message" class="h-4 w-4"/></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><p class="font-semibold">{{ $response->title }}</p>@if ($response->shortcut)<span class="badge bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $response->shortcut }}</span>@endif</div>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $response->body }}</p>
                        </div>
                        <button type="button" class="text-sm font-semibold text-blue-600" @click="edit = true">Modifier</button>
                        <form method="POST" action="{{ route('admin.canned.delete', $response) }}" @submit.prevent="$store.confirm.ask($el, 'Supprimer cette réponse ?', 'Elle ne sera plus proposée aux agents.')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 transition hover:text-red-600" aria-label="Supprimer"><x-icon n="x" class="h-4 w-4"/></button>
                        </form>
                    </div>
                    <form method="POST" action="{{ route('admin.canned.save') }}" x-show="edit" x-cloak class="space-y-2">
                        @csrf
                        <input type="hidden" name="id" value="{{ $response->id }}">
                        <div class="grid gap-2 sm:grid-cols-[1fr_150px]"><input name="title" value="{{ $response->title }}" class="input" required><input name="shortcut" value="{{ $response->shortcut }}" class="input" placeholder="/raccourci"></div>
                        <textarea name="body" rows="4" class="input" required>{{ $response->body }}</textarea>
                        <div class="flex gap-2"><button class="btn-primary">Enregistrer</button><button type="button" class="btn-ghost" @click="edit = false">Annuler</button></div>
                    </form>
                </li>
            @empty
                <li class="p-10"><x-empty-state icon="message" title="Aucune réponse" text="Créez votre premier modèle pour accélérer les réponses."/></li>
            @endforelse
        </ul>
    </div>

    <form method="POST" action="{{ route('admin.canned.save') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <div><h2 class="font-display text-lg font-bold">Nouvelle réponse</h2><p class="text-sm text-slate-500">Le raccourci est facultatif, par exemple <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">/accuse</code>.</p></div>
        <div><label class="label" for="title">Titre</label><input id="title" name="title" class="input" placeholder="Ex. : Accusé de réception" required></div>
        <div><label class="label" for="shortcut">Raccourci</label><input id="shortcut" name="shortcut" class="input" placeholder="/accuse"></div>
        <div><label class="label" for="body">Texte</label><textarea id="body" name="body" rows="5" class="input" placeholder="Bonjour, …" required></textarea></div>
        <button class="btn-primary w-full"><x-icon n="plus" class="h-4 w-4"/>Ajouter la réponse</button>
    </form>
</div>
@endsection
