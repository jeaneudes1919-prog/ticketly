@extends('layouts.app')
@section('title', 'Rapports')
@section('content')
<div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div><h1 class="font-display text-2xl font-bold sm:text-3xl">Rapports</h1><p class="text-slate-500">Analyse de la performance sur la période choisie.</p></div>
    <form method="GET" class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-end sm:gap-3">
        <div><label class="label">Du</label><input type="date" name="from" value="{{ $from }}" class="input"></div>
        <div><label class="label">Au</label><input type="date" name="to" value="{{ $to }}" class="input"></div>
        <button class="btn-primary col-span-1"><x-icon n="filter" class="h-4 w-4"/>Appliquer</button>
        <a href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}" class="btn-ghost col-span-1"><x-icon n="download" class="h-4 w-4"/>CSV</a>
    </form>
</div>
<div class="mt-5 grid grid-cols-2 gap-3 sm:mt-6 sm:gap-4 lg:grid-cols-4"><x-stat label="Tickets reçus" :value="$stats['volume']" :i="0"/><x-stat label="Tickets résolus" :value="$stats['resolved']" tone="green" :i="1"/><x-stat label="Résolution moy. (h)" :value="$stats['avg_resolution_h']" tone="violet" :decimals="1" :i="2"/><x-stat label="SLA respecté" :value="$stats['sla_ok']" suffix=" %" tone="amber" :i="3"/></div>
<div class="card mt-5 overflow-hidden sm:mt-6">
    <div class="hidden overflow-x-auto sm:block">
        <table class="w-full min-w-[480px]"><thead class="bg-slate-50 dark:bg-slate-800/50"><tr><th class="th">Agent</th><th class="th">Reçus</th><th class="th">Résolus</th><th class="th">SLA dépassé</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">@forelse ($byAgent as $a)<tr><td class="px-4 py-3 font-medium">{{ $a['name'] }}</td><td class="px-4 py-3">{{ $a['total'] }}</td><td class="px-4 py-3 text-emerald-600">{{ $a['resolved'] }}</td><td class="px-4 py-3 {{ $a['late'] ? 'text-red-600' : 'text-slate-400' }}">{{ $a['late'] }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">Aucune donnée sur cette période.</td></tr>@endforelse</tbody></table>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800 sm:hidden">
        @forelse ($byAgent as $a)
            <div class="p-4">
                <div class="flex items-center justify-between gap-2"><p class="font-semibold">{{ $a['name'] }}</p><span class="badge {{ $a['late'] ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">SLA {{ $a['late'] ? 'dépassé' : 'respecté' }}</span></div>
                <div class="mt-2 grid grid-cols-3 gap-2 text-xs text-slate-500">
                    <div><span class="block text-[10px] uppercase tracking-wide">Reçus</span><b class="text-sm text-slate-800 dark:text-slate-200">{{ $a['total'] }}</b></div>
                    <div><span class="block text-[10px] uppercase tracking-wide">Résolus</span><b class="text-sm text-emerald-600">{{ $a['resolved'] }}</b></div>
                    <div><span class="block text-[10px] uppercase tracking-wide">En retard</span><b class="text-sm {{ $a['late'] ? 'text-red-600' : 'text-slate-400' }}">{{ $a['late'] }}</b></div>
                </div>
            </div>
        @empty
            <p class="px-4 py-12 text-center text-slate-500">Aucune donnée sur cette période.</p>
        @endforelse
    </div>
</div>
@endsection
