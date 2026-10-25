@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="font-display text-3xl font-bold">Tableau de bord</h1><p class="text-slate-500">Vue d'ensemble de l'activité du support.</p></div><a href="{{ route('tickets.index', ['view' => 'late']) }}" class="btn-ghost">Voir les tickets en retard</a></div>
<div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
    <x-stat label="Ouverts" :value="$kpis['open']" :i="0"/><x-stat label="Non assignés" :value="$kpis['unassigned']" tone="amber" :i="1"/><x-stat label="SLA dépassé" :value="$kpis['late']" tone="red" :i="2"/>
    <x-stat label="Résolus aujourd'hui" :value="$kpis['resolved_today']" tone="green" :i="3"/><x-stat label="1re réponse (min)" :value="$kpis['avg_first_response']" tone="violet" :i="4"/><x-stat label="Satisfaction /5" :value="$kpis['satisfaction']" tone="amber" :decimals="1" :i="5"/>
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card p-5 xl:col-span-2"><h2 class="font-display text-lg font-bold">Tickets créés et résolus (30 jours)</h2><div class="relative mt-4 h-64 sm:h-72"><canvas id="cLine"></canvas></div></div>
    <div class="card p-5"><h2 class="font-display text-lg font-bold">Répartition par statut</h2><div class="relative mx-auto mt-4 h-64 max-w-xs"><canvas id="cStatus"></canvas></div></div>
    <div class="card p-5"><h2 class="font-display text-lg font-bold">Tickets ouverts par priorité</h2><div class="relative mt-4 h-56"><canvas id="cPrio"></canvas></div></div>
    <div class="card p-5"><h2 class="font-display text-lg font-bold">Par catégorie</h2><div class="relative mt-4 h-56"><canvas id="cCat"></canvas></div></div>
    <div class="card p-5"><h2 class="font-display text-lg font-bold">Classement des agents</h2>
        <ul class="mt-4 space-y-3">@foreach ($leaders as $i => $l)<li class="flex items-center gap-3"><span class="w-5 text-center font-display font-bold text-slate-400">{{ $i + 1 }}</span><span class="grid h-9 w-9 place-items-center rounded-full text-sm font-bold text-white" style="background:{{ $l['color'] }}">{{ mb_substr($l['name'], 0, 1) }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $l['name'] }}</p><p class="text-xs text-slate-500">{{ $l['open'] }} en cours</p></div><b class="text-emerald-600">{{ $l['resolved'] }}</b></li>@endforeach</ul><p class="mt-3 text-xs text-slate-400">Tickets résolus sur 30 jours</p></div>
</div>
<div class="card mt-6 overflow-hidden"><div class="flex items-center justify-between p-5"><h2 class="font-display text-lg font-bold">À traiter en premier</h2><a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-blue-600">Tout voir</a></div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($urgent as $t)
            <a href="{{ route('tickets.show', $t) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/40"><div class="min-w-0"><p class="text-xs font-semibold text-blue-600">{{ $t->reference }} · {{ $t->customer->name }}</p><p class="truncate font-medium">{{ $t->subject }}</p></div><div class="flex items-center gap-2"><span class="badge {{ $t->priority->color() }}">{{ $t->priority->label() }}</span><x-sla :t="$t"/></div></a>
        @empty
            <p class="px-5 pb-8 text-center text-slate-500">Aucun ticket ouvert. Beau travail ! 🎉</p>
        @endforelse
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const d = @js($charts);
    const dark = document.documentElement.classList.contains('dark');
    Chart.defaults.color = dark ? '#94a3b8' : '#64748b';
    Chart.defaults.font.family = '"Inter Variable", sans-serif';
    const grid = { color: dark ? 'rgba(148,163,184,.12)' : 'rgba(100,116,139,.12)' };
    new Chart(cLine, { type: 'line', data: { labels: d.labels, datasets: [
        { label: 'Créés', data: d.created, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.15)', fill: true, tension: .4, pointRadius: 0 },
        { label: 'Résolus', data: d.resolved, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: .4, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }, y: { grid, beginAtZero: true, ticks: { precision: 0 } } } } });
    new Chart(cStatus, { type: 'doughnut', data: { labels: d.status.map(s => s.label), datasets: [{ data: d.status.map(s => s.n), backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#94a3b8'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } } } });
    new Chart(cPrio, { type: 'bar', data: { labels: d.priority.map(s => s.label), datasets: [{ data: d.priority.map(s => s.n), backgroundColor: ['#94a3b8', '#38bdf8', '#fb923c', '#ef4444'], borderRadius: 8 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid, beginAtZero: true, ticks: { precision: 0 } } } } });
    new Chart(cCat, { type: 'bar', data: { labels: d.category.map(s => s.label), datasets: [{ data: d.category.map(s => s.n), backgroundColor: '#8b5cf6', borderRadius: 8 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { display: false } }, x: { grid, beginAtZero: true, ticks: { precision: 0 } } } } });
});
</script>
@endpush
