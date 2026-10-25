@extends('layouts.app')
@section('title', 'Tickets')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Boîte de réception</p>
        <h1 class="mt-1 font-display text-3xl font-bold">Tickets</h1>
        <p class="text-slate-500">Triés par urgence et par échéance SLA.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('reports') }}" class="btn-ghost"><x-icon n="chart" class="h-4 w-4"/>Rapports</a>
        <a href="{{ route('reports.export') }}" class="btn-ghost"><x-icon n="download" class="h-4 w-4"/>Export CSV</a>
    </div>
</div>

<div class="mt-6 flex gap-2 overflow-x-auto pb-1">
    @foreach (['all' => 'Tous', 'mine' => 'Mes tickets', 'unassigned' => 'Non assignés', 'late' => 'En retard', 'resolved' => 'Résolus'] as $key => $label)
        <a href="{{ route('tickets.index', array_filter(['view' => $key, 'q' => request('q'), 'status' => request('status'), 'priority' => request('priority'), 'category_id' => request('category_id'), 'assignee_id' => request('assignee_id'), 'tag' => request('tag'), 'period' => request('period'), 'sort' => request('sort')])) }}"
           class="flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $view === $key ? 'bg-slate-900 text-white shadow-lg dark:bg-white dark:text-slate-900' : 'bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800' }}">
            {{ $label }}
            <span class="rounded-full bg-black/10 px-2 text-xs dark:bg-white/20">{{ $counts[$key] }}</span>
            @if ($key === 'late' && $counts[$key] > 0)<span class="h-2 w-2 animate-pulse rounded-full bg-red-400"></span>@endif
        </a>
    @endforeach
</div>

<form method="GET" class="card mt-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-9">
    <input type="hidden" name="view" value="{{ $view }}">
    <input name="q" value="{{ request('q') }}" class="input xl:col-span-2" placeholder="Référence, sujet ou client">
    <select name="status" class="input">
        <option value="">Tous statuts</option>
        @foreach (\App\Enums\TicketStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <select name="priority" class="input">
        <option value="">Toutes priorités</option>
        @foreach (\App\Enums\TicketPriority::cases() as $priority)
            <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
        @endforeach
    </select>
    <select name="category_id" class="input">
        <option value="">Toutes catégories</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <select name="assignee_id" class="input">
        <option value="">Tous les agents</option>
        @foreach ($agents as $agent)
            <option value="{{ $agent->id }}" @selected(request('assignee_id') == $agent->id)>{{ $agent->name }}</option>
        @endforeach
    </select>
    <select name="tag" class="input">
        <option value="">Toutes étiquettes</option>
        @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @selected(request('tag') == $tag->id)>{{ $tag->name }}</option>
        @endforeach
    </select>
    <select name="period" class="input">
        <option value="">Toute période</option>
        <option value="7" @selected(request('period') === '7')>7 derniers jours</option>
        <option value="30" @selected(request('period') === '30')>30 derniers jours</option>
        <option value="90" @selected(request('period') === '90')>90 derniers jours</option>
    </select>
    <select name="sort" class="input">
        <option value="">Tri : urgence</option>
        <option value="recent" @selected(request('sort') === 'recent')>Tri : plus récents</option>
        <option value="oldest" @selected(request('sort') === 'oldest')>Tri : plus anciens</option>
    </select>
    <div class="flex gap-2 sm:col-span-2 lg:col-span-4 xl:col-span-9">
        <button class="btn-primary"><x-icon n="filter" class="h-4 w-4"/>Filtrer</button>
        <a href="{{ route('tickets.index') }}" class="btn-ghost">Effacer les filtres</a>
    </div>
</form>

<form method="POST" action="{{ route('tickets.bulk') }}" x-data="{ sel: [], action: 'assign' }" class="mt-4">
    @csrf
    <div x-show="sel.length" x-cloak x-transition class="card mb-3 flex flex-wrap items-center gap-3 border-blue-300 bg-blue-50 p-3 dark:border-blue-500/40 dark:bg-blue-500/10">
        <b class="text-sm" x-text="sel.length + ' sélectionné(s)'"></b>
        <select name="action" x-model="action" class="input !w-auto">
            <option value="assign">Assigner à…</option>
            <option value="status">Changer le statut…</option>
            <option value="close">Fermer les tickets</option>
        </select>
        <select x-show="action === 'assign'" :name="action === 'assign' ? 'value' : ''" class="input !w-auto">
            @foreach ($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach
        </select>
        <select x-show="action === 'status'" :name="action === 'status' ? 'value' : ''" class="input !w-auto">
            @foreach (\App\Enums\TicketStatus::cases() as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach
        </select>
        <span x-show="action === 'close'" class="text-sm text-slate-500">Les tickets seront marqués comme fermés.</span>
        <button class="btn-primary">Appliquer</button>
    </div>

    <div class="space-y-3 md:card md:overflow-hidden md:space-y-0">
        <table class="w-full md:divide-y md:divide-slate-100 dark:md:divide-slate-800">
            <thead class="hidden bg-slate-50 md:table-header-group dark:bg-slate-800/50">
                <tr><th class="th w-10"></th><th class="th">Ticket</th><th class="th">Client</th><th class="th">Priorité</th><th class="th">Statut</th><th class="th">Assigné</th><th class="th">SLA</th></tr>
            </thead>
            <tbody class="block md:table-row-group md:divide-y md:divide-slate-100 dark:md:divide-slate-800">
            @forelse ($tickets as $index => $ticket)
                <tr class="fade-up card mb-3 block p-4 transition hover:bg-slate-50 md:mb-0 md:table-row md:rounded-none md:border-0 md:p-0 md:shadow-none dark:hover:bg-slate-800/40" style="--i:{{ $index }}">
                    <td class="block md:table-cell md:px-4 md:py-3"><input type="checkbox" name="ids[]" value="{{ $ticket->id }}" x-model="sel" class="rounded border-slate-300 text-blue-600" aria-label="Sélectionner {{ $ticket->reference }}"></td>
                    <td class="block md:table-cell md:px-4 md:py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="block">
                            <span class="text-xs font-semibold text-blue-600">{{ $ticket->reference }}</span>
                            <span class="block font-semibold hover:text-blue-600">{{ $ticket->subject }}</span>
                            <span class="mt-1 flex flex-wrap gap-1">@foreach ($ticket->tags as $tag)<span class="badge text-white" style="background:{{ $tag->color }}">{{ $tag->name }}</span>@endforeach</span>
                        </a>
                    </td>
                    <td class="block text-sm md:table-cell md:px-4 md:py-3"><span class="text-slate-500 md:hidden">Client : </span>{{ $ticket->customer->name }}</td>
                    <td class="inline-block pr-2 md:table-cell md:px-4 md:py-3"><span class="badge {{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</span></td>
                    <td class="inline-block md:table-cell md:px-4 md:py-3"><span class="badge {{ $ticket->status->color() }}">{{ $ticket->status->label() }}</span></td>
                    <td class="block text-sm md:table-cell md:px-4 md:py-3">
                        @if ($ticket->assignee)
                            <span class="flex items-center gap-2"><x-avatar :user="$ticket->assignee" :size="6"/>{{ $ticket->assignee->name }}</span>
                        @else
                            <span class="text-slate-400">Non assigné</span>
                        @endif
                    </td>
                    <td class="block pt-2 md:table-cell md:px-4 md:py-3 md:pt-3"><x-sla :t="$ticket"/></td>
                </tr>
            @empty
                <tr class="block md:table-row"><td colspan="7" class="block px-6 py-16 text-center md:table-cell">
                    <x-empty-state icon="inbox" title="Aucun ticket ne correspond" text="Modifiez vos filtres pour élargir la recherche.">
                        <a href="{{ route('tickets.index') }}" class="btn-ghost">Réinitialiser les filtres</a>
                    </x-empty-state>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</form>
<div class="mt-6">{{ $tickets->links('components.pagination') }}</div>
@endsection
