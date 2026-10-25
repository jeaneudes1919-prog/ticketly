@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="font-display text-2xl font-bold sm:text-3xl">Clients</h1>
    <form method="GET" class="w-full sm:w-auto"><input name="q" value="{{ request('q') }}" class="input w-full sm:w-64" placeholder="Nom ou email"></form>
</div>
<div class="mt-5 grid grid-cols-1 gap-3 sm:mt-6 sm:grid-cols-2 sm:gap-4 xl:grid-cols-3">
    @forelse ($customers as $i => $c)
        <div class="card fade-up flex min-w-0 flex-col gap-3 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5" style="--i:{{ $i }}">
            <div class="flex min-w-0 items-center gap-3">
                <x-avatar :user="$c" :size="10"/>
                <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $c->name }}</p><p class="truncate text-sm text-slate-500">{{ $c->email }}</p></div>
            </div>
            <a href="{{ route('tickets.index', ['q' => $c->name]) }}" class="badge shrink-0 self-start bg-blue-100 text-blue-700 sm:ml-auto">{{ $c->tickets_count }} ticket(s)</a>
        </div>
    @empty
        <p class="col-span-full py-16 text-center text-slate-500">Aucun client trouvé.</p>
    @endforelse
</div>
<div class="mt-6">{{ $customers->links('components.pagination') }}</div>
@endsection
