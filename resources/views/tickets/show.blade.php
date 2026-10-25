@extends('layouts.app')
@section('title', $ticket->reference)
@section('content')
<a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-blue-600">← Tickets</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="text-sm font-semibold text-blue-600">{{ $ticket->reference }}</p>
        <h1 class="font-display text-2xl font-bold sm:text-3xl">{{ $ticket->subject }}</h1>
        <p class="mt-1 text-sm text-slate-500">Créé {{ $ticket->created_at->translatedFormat('d M Y à H:i') }} par {{ $ticket->customer->name }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="badge {{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</span>
        <span class="badge {{ $ticket->status->color() }}">{{ $ticket->status->label() }}</span>
        <x-sla-timer :t="$ticket"/>
    </div>
</div>

@if ($ticket->sla_breached && ! $ticket->isFinal())
    <div class="mt-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        <x-icon n="alert" class="mt-0.5 h-5 w-5 shrink-0"/>
        <div><b>SLA dépassé.</b> Ce ticket a dépassé son échéance de résolution ou de première réponse. Une action est attendue rapidement.</div>
    </div>
@endif

<div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px] lg:gap-6">
    <div class="min-w-0 space-y-6">
        @include('partials.thread', ['ticket' => $ticket])

        <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data"
              x-data="{
                  internal: false,
                  body: @js(old('body', '')),
                  canned: @js($canned->mapWithKeys(fn ($c) => [($c->shortcut ?: $c->title) => $c->body])),
                  shortcuts: @js($canned->filter(fn ($c) => $c->shortcut)->mapWithKeys(fn ($c) => [$c->shortcut => $c->body])),
                  insert(text) { if (text) this.body += (this.body ? '\n\n' : '') + text; },
                  expandShortcut() {
                      const match = this.body.match(/(^|\s)(\/[a-z0-9-]+)\s*$/i);
                      if (match && this.shortcuts[match[2]]) {
                          this.body = this.body.replace(match[0], (match[1] || '') + this.shortcuts[match[2]]);
                      }
                  }
              }"
              class="card p-4 sm:p-5" :class="internal && 'border-amber-300 bg-amber-50/60 dark:border-amber-500/40 dark:bg-amber-500/5'">
            @csrf
            <input type="hidden" name="is_internal" :value="internal ? 1 : 0">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                    <button type="button" @click="internal = false" :class="! internal && 'bg-white shadow dark:bg-slate-700'" class="rounded-lg px-4 py-1.5 text-sm font-semibold">Répondre au client</button>
                    <button type="button" @click="internal = true" :class="internal && 'bg-amber-200 text-amber-900 shadow'" class="rounded-lg px-4 py-1.5 text-sm font-semibold">Note interne</button>
                </div>
                <div class="flex items-center gap-2">
                    <select class="input !w-auto max-w-[240px] text-xs" @change="insert(canned[$event.target.value]); $event.target.value = ''">
                        <option value="">Réponse prédéfinie…</option>
                        @foreach ($canned as $response)
                            <option value="{{ $response->shortcut ?: $response->title }}">{{ $response->shortcut ? $response->shortcut.' — ' : '' }}{{ $response->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <textarea name="body" x-model="body" @input="expandShortcut()" rows="6" class="input" :placeholder="internal ? 'Note visible uniquement par l’équipe…' : 'Votre réponse au client…'" required></textarea>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-400">Astuce : tapez <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">/accuse</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">/resolu</code> ou <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">/facture</code> puis espace pour insérer un modèle.</p>
            <div class="mt-3">@include('partials.dropzone')</div>
            <div class="mt-4 flex justify-end">
                <button class="btn-primary" :class="internal && '!from-amber-500 !to-orange-500'">
                    <x-icon n="send" class="h-4 w-4"/><span x-text="internal ? 'Ajouter la note' : 'Envoyer la réponse'"></span>
                </button>
            </div>
        </form>
    </div>

    <aside class="space-y-5">
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="card space-y-4 p-4 sm:p-5">
            @csrf @method('PATCH')
            <input type="hidden" name="tags_present" value="1">
            <h2 class="font-display text-lg font-bold">Propriétés</h2>
            <div>
                <label class="label" for="status">Statut</label>
                <select id="status" name="status" class="input">
                    @foreach (\App\Enums\TicketStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($ticket->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="priority">Priorité</label>
                <select id="priority" name="priority" class="input">
                    @foreach (\App\Enums\TicketPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected($ticket->priority === $priority)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="assignee_id">Assigné à</label>
                <select id="assignee_id" name="assignee_id" class="input">
                    <option value="">Non assigné</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" @selected($ticket->assignee_id === $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="category_id">Catégorie</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">—</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($ticket->category_id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="department_id">Département</label>
                    <select id="department_id" name="department_id" class="input">
                        <option value="">—</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($ticket->department_id === $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label">Étiquettes</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="peer hidden" @checked($ticket->tags->contains($tag->id))>
                            <span class="badge border-2 border-transparent text-white opacity-40 transition peer-checked:opacity-100" style="background:{{ $tag->color }}">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button class="btn-primary w-full">Enregistrer les modifications</button>
        </form>

        <div class="card p-4 sm:p-5">
            <h2 class="font-display text-lg font-bold">Client</h2>
            <div class="mt-3 flex items-center gap-3">
                <x-avatar :user="$ticket->customer" :size="11"/>
                <div class="min-w-0"><p class="font-semibold">{{ $ticket->customer->name }}</p><p class="truncate text-sm text-slate-500">{{ $ticket->customer->email }}</p></div>
            </div>
            @if ($history->count())
                <p class="label mt-4">Autres tickets</p>
                <ul class="space-y-1.5 text-sm">
                    @foreach ($history as $item)
                        <li><a href="{{ route('tickets.show', $item) }}" class="flex justify-between gap-2 hover:text-blue-600"><span class="truncate">{{ $item->reference }} · {{ $item->subject }}</span><span class="badge shrink-0 {{ $item->status->color() }}">{{ $item->status->label() }}</span></a></li>
                    @endforeach
                </ul>
            @endif
            @if ($ticket->satisfaction)
                <p class="mt-4 text-sm">Satisfaction : <b class="text-amber-500">{{ str_repeat('★', $ticket->satisfaction) }}</b><span class="text-slate-300">{{ str_repeat('★', 5 - $ticket->satisfaction) }}</span></p>
            @endif
        </div>

        @if ($mergeTargets->isNotEmpty() && ! $ticket->isFinal())
            <div class="card p-4 sm:p-5">
                <h2 class="font-display text-lg font-bold">Fusionner ce ticket</h2>
                <p class="mt-1 text-sm text-slate-500">Déplacez les réponses et pièces jointes vers un autre ticket ouvert du même client. Ce ticket sera fermé.</p>
                <form method="POST" action="{{ route('tickets.merge', $ticket) }}" class="mt-3 space-y-3"
                      x-data="{ target: '' }"
                      @submit.prevent="if (! target) return; $store.confirm.ask($el, 'Fusionner ce ticket ?', 'Les réponses et pièces jointes seront déplacées, puis ce ticket sera fermé.', true, 'Fusionner')">
                    @csrf
                    <select name="target_id" x-model="target" class="input" required>
                        <option value="">Choisir le ticket de destination…</option>
                        @foreach ($mergeTargets as $target)
                            <option value="{{ $target->id }}">{{ $target->reference }} · {{ $target->subject }}</option>
                        @endforeach
                    </select>
                    <button class="btn-ghost w-full text-amber-700 dark:text-amber-300" :disabled="! target"><x-icon n="filter" class="h-4 w-4"/>Fusionner</button>
                </form>
            </div>
        @endif

        <div class="card p-4 sm:p-5">
            <h2 class="font-display text-lg font-bold">Activité</h2>
            <ol class="relative mt-4 space-y-4 border-l-2 border-slate-200 pl-5 dark:border-slate-700">
                @php
                    $labels = [
                        'created' => 'Ticket créé', 'replied' => 'Réponse envoyée', 'internal_note' => 'Note interne ajoutée',
                        'status_changed' => 'Statut modifié', 'priority_changed' => 'Priorité modifiée',
                        'assignee_id_changed' => 'Assignation modifiée', 'category_id_changed' => 'Catégorie modifiée',
                        'department_id_changed' => 'Département modifié', 'tags_changed' => 'Étiquettes modifiées',
                        'sla_breached' => 'SLA dépassé', 'sla_warning' => 'Alerte SLA', 'closed_by_customer' => 'Fermé par le client',
                        'reopened' => 'Rouvert par le client', 'auto_closed' => 'Fermé automatiquement', 'rated' => 'Évaluation reçue',
                        'merged_from' => 'Ticket fusionné ici', 'merged_into' => 'Fusionné dans un autre ticket',
                    ];
                @endphp
                @foreach ($ticket->logs->take(14) as $log)
                    <li class="relative text-sm">
                        <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full border-2 border-white bg-blue-500 dark:border-slate-900"></span>
                        <p class="font-medium">{{ $labels[$log->action] ?? $log->action }}</p>
                        <p class="text-xs text-slate-500">{{ $log->user?->name ?? 'Système' }} · {{ $log->created_at->diffForHumans() }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </aside>
</div>
@endsection
