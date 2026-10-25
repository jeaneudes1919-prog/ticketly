@extends('layouts.app')
@section('title', 'Catalogue')
@section('content')
<h1 class="font-display text-3xl font-bold">Catalogue</h1><p class="text-slate-500">Catégories, départements et étiquettes utilisés pour classer les tickets.</p>
<div class="mt-6 grid gap-6 lg:grid-cols-3">
@foreach ([['categories', 'Catégories', $categories, true], ['departments', 'Départements', $departments, false], ['tags', 'Étiquettes', $tags, true]] as [$type, $title, $items, $color])
    <section class="card p-5" x-data="{ edit: null }">
        <h2 class="font-display text-lg font-bold">{{ $title }}</h2>
        <ul class="mt-4 space-y-2">
            @foreach ($items as $it)
                <li class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                    @if ($color)<span class="h-3 w-3 shrink-0 rounded-full" style="background:{{ $it->color }}"></span>@endif
                    <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $it->name }}</span>
                    <form method="POST" action="{{ route('admin.catalog.save', $type) }}" class="flex items-center gap-1" x-show="edit === {{ $it->id }}" x-cloak>@csrf<input type="hidden" name="id" value="{{ $it->id }}"><input name="name" value="{{ $it->name }}" class="input !w-28 !py-1.5">@if ($color)<input type="color" name="color" value="{{ $it->color }}" class="h-8 w-8 rounded">@endif<button class="text-emerald-600" aria-label="Enregistrer"><x-icon n="check" class="h-5 w-5"/></button></form>
                    <button type="button" class="text-slate-400 hover:text-blue-600" x-show="edit !== {{ $it->id }}" @click="edit = {{ $it->id }}" aria-label="Modifier">✎</button>
                    <form method="POST" action="{{ route('admin.catalog.delete', [$type, $it->id]) }}" @submit.prevent="$store.confirm.ask($el, 'Supprimer « {{ addslashes($it->name) }} » ?', 'Les tickets associés seront conservés mais perdront ce classement.')">@csrf @method('DELETE')<button class="text-slate-400 hover:text-red-600" aria-label="Supprimer"><x-icon n="x" class="h-4 w-4"/></button></form>
                </li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('admin.catalog.save', $type) }}" class="mt-4 flex gap-2">@csrf<input name="name" class="input" placeholder="Ajouter…" required>@if ($color)<input type="color" name="color" value="#3B82F6" class="h-11 w-12 shrink-0 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700">@endif<button class="btn-primary !px-4" aria-label="Ajouter"><x-icon n="plus" class="h-5 w-5"/></button></form>
    </section>
@endforeach
</div>
@endsection
