@extends('layouts.app')
@section('title', 'Tickets')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Boîte de réception</p>
        <h1 class="mt-1 font-display text-2xl sm:text-3xl font-bold">Tickets</h1>
        <p class="text-slate-500">Triés par urgence et par échéance SLA.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('reports') }}" class="btn-ghost"><x-icon n="chart" class="h-4 w-4"/>Rapports</a>
        <a href="{{ route('reports.export') }}" class="btn-ghost"><x-icon n="download" class="h-4 w-4"/>Export CSV</a>
    </div>
</div>

<div x-data="ticketInbox">
<div class="mt-6 flex flex-wrap gap-2 pb-1" x-ref="quickViews">
    @foreach (['all' => 'Tous', 'mine' => 'Mes tickets', 'unassigned' => 'Non assignés', 'late' => 'En retard', 'resolved' => 'Résolus'] as $key => $label)
        <a href="{{ route('tickets.index', array_filter(['view' => $key, 'q' => request('q'), 'status' => request('status'), 'priority' => request('priority'), 'category_id' => request('category_id'), 'assignee_id' => request('assignee_id'), 'tag' => request('tag'), 'period' => request('period'), 'sort' => request('sort')])) }}"
           data-view="{{ $key }}"
           class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[13px] font-semibold transition sm:gap-2 sm:px-4 sm:py-2 sm:text-sm {{ $view === $key ? 'bg-slate-900 text-white shadow-lg dark:bg-white dark:text-slate-900' : 'bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800' }}">
            {{ $label }}
            <span class="rounded-full bg-black/10 px-2 text-xs dark:bg-white/20">{{ $counts[$key] }}</span>
            @if ($key === 'late' && $counts[$key] > 0)<span class="h-2 w-2 animate-pulse rounded-full bg-red-400"></span>@endif
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('tickets.index') }}" x-ref="filters" @submit.prevent="apply()" class="card mt-4 p-3 sm:p-4">
    <input type="hidden" name="view" value="{{ $view }}">
    <div class="flex items-center gap-2">
        <div class="relative min-w-0 flex-1">
            <span class="pointer-events-none absolute left-3 top-2.5 text-slate-400"><x-icon n="search" class="h-5 w-5"/></span>
            <input name="q" value="{{ request('q') }}" class="input !pl-10" placeholder="Rechercher…"
                   aria-label="Rechercher un ticket" autocomplete="off" enterkeyhint="search" @input="onSearch()">
        </div>
        <button type="button" class="btn-ghost shrink-0 lg:hidden" @click="filtersOpen = ! filtersOpen"
                :aria-expanded="filtersOpen" aria-controls="ticket-filters">
            <x-icon n="filter" class="h-4 w-4"/>Filtres
            <span x-show="activeFilters > 0" x-cloak class="grid h-5 min-w-5 place-items-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white" x-text="activeFilters"></span>
        </button>
    </div>

    <div id="ticket-filters" x-show="filtersOpen || desktop" x-cloak x-transition.opacity
         class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3 lg:grid-cols-3 xl:grid-cols-4">
        <select name="status" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Tous statuts</option>
            @foreach (\App\Enums\TicketStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="priority" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Toutes priorités</option>
            @foreach (\App\Enums\TicketPriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
        <select name="category_id" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Toutes catégories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="assignee_id" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Tous les agents</option>
            @foreach ($agents as $agent)
                <option value="{{ $agent->id }}" @selected(request('assignee_id') == $agent->id)>{{ $agent->name }}</option>
            @endforeach
        </select>
        <select name="tag" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Toutes étiquettes</option>
            @foreach ($tags as $tag)
                <option value="{{ $tag->id }}" @selected(request('tag') == $tag->id)>{{ $tag->name }}</option>
            @endforeach
        </select>
        <select name="period" class="input" @change="activeFilters = countActive(); apply()">
            <option value="">Toute période</option>
            <option value="7" @selected(request('period') === '7')>7 derniers jours</option>
            <option value="30" @selected(request('period') === '30')>30 derniers jours</option>
            <option value="90" @selected(request('period') === '90')>90 derniers jours</option>
        </select>
        <select name="sort" class="input" @change="apply()">
            <option value="">Tri : urgence</option>
            <option value="recent" @selected(request('sort') === 'recent')>Tri : plus récents</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Tri : plus anciens</option>
        </select>
    </div>

    <div x-show="filtersOpen || desktop" x-cloak class="mt-3 flex flex-wrap items-center gap-2">
        <button type="button" class="btn-primary lg:hidden" @click="showResults()">
            <x-icon n="filter" class="h-4 w-4"/>Voir les résultats
        </button>
        <button type="button" class="btn-ghost" @click="reset()">Effacer les filtres</button>
        <span x-show="loading" x-cloak class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500">
            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-blue-600"></span>Mise à jour…
        </span>
        <span class="ml-auto hidden text-xs text-slate-400 lg:block">Les filtres s’appliquent automatiquement.</span>
    </div>
</form>

<div class="relative mt-4">
    <div x-show="loading" x-cloak class="pointer-events-none absolute -top-2 right-0 z-10 flex items-center gap-2 rounded-full border border-slate-200 bg-white/95 px-3 py-1 text-xs font-semibold text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/95">
        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-blue-600"></span>Mise à jour…
    </div>
    <div x-ref="results" @click="handleResultClick($event)" :aria-busy="loading" :class="loading && 'opacity-60'"
         class="transition-opacity duration-200" aria-live="polite">
        @include('tickets._results')
    </div>
</div>
</div>
@endsection
