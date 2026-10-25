@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3"><h1 class="font-display text-3xl font-bold">Clients</h1>
    <form method="GET"><input name="q" value="{{ request('q') }}" class="input" placeholder="Nom ou email"></form></div>
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($customers as $i => $c)
        <div class="card fade-up flex items-center gap-4 p-5" style="--i:{{ $i }}"><x-avatar :user="$c" :size="12"/><div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $c->name }}</p><p class="truncate text-sm text-slate-500">{{ $c->email }}</p></div><a href="{{ route('tickets.index', ['q' => $c->name]) }}" class="badge bg-blue-100 text-blue-700">{{ $c->tickets_count }} ticket(s)</a></div>
    @empty
        <p class="col-span-full py-16 text-center text-slate-500">Aucun client trouvé.</p>
    @endforelse
</div>
<div class="mt-6">{{ $customers->links('components.pagination') }}</div>
@endsection
