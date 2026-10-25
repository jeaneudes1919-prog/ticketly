@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Administration</p>
        <h1 class="mt-1 font-display text-2xl sm:text-3xl font-bold">{{ $title }}</h1>
        <p class="text-slate-500">Gérez les éléments utilisés pour classer et router les tickets.</p>
    </div>
    <a href="{{ route('admin.catalog') }}" class="btn-ghost">Vue d’ensemble</a>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_360px] lg:gap-6">
    <div class="card overflow-hidden">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($items as $item)
                <li class="p-3.5 sm:p-4" x-data="{ edit: false }">
                    <div class="flex items-center gap-3" x-show="! edit">
                        @if ($color)
                            <span class="h-3 w-3 shrink-0 rounded-full ring-4 ring-slate-100 dark:ring-slate-800" style="background:{{ $item->color }}"></span>
                        @else
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><x-icon n="building" class="h-4 w-4"/></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $item->name }}</p>
                            @if (! $color)
                                <p class="truncate text-xs text-slate-500">{{ $item->description ?: 'Aucune description' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $item->agents_count ?? 0 }} agent(s) rattaché(s)</p>
                            @else
                                <p class="text-xs text-slate-400">{{ $item->color }}</p>
                            @endif
                        </div>
                        <button type="button" class="text-sm font-semibold text-blue-600" @click="edit = true">Modifier</button>
                        <form method="POST" action="{{ route('admin.catalog.delete', [$type, $item->id]) }}"
                              @submit.prevent="$store.confirm.ask($el, 'Supprimer « {{ addslashes($item->name) }} » ?', 'Les tickets associés seront conservés mais perdront ce classement.')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 transition hover:text-red-600" aria-label="Supprimer"><x-icon n="x" class="h-4 w-4"/></button>
                        </form>
                    </div>
                    <form method="POST" action="{{ route('admin.catalog.save', $type) }}" x-show="edit" x-cloak class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <input name="name" value="{{ $item->name }}" class="input min-w-[160px] flex-1" required>
                        @if ($color)
                            <input type="color" name="color" value="{{ $item->color }}" class="h-11 w-12 shrink-0 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700">
                        @else
                            <input name="description" value="{{ $item->description }}" class="input min-w-[200px] flex-1" placeholder="Description">
                        @endif
                        <button class="btn-primary !px-4" aria-label="Enregistrer"><x-icon n="check" class="h-5 w-5"/></button>
                        <button type="button" class="btn-ghost !px-4" @click="edit = false" aria-label="Annuler"><x-icon n="x" class="h-5 w-5"/></button>
                    </form>
                </li>
            @empty
                <li class="p-10">
                    <x-empty-state icon="tag" title="Aucun élément" text="Ajoutez votre premier élément avec le formulaire à droite."/>
                </li>
            @endforelse
        </ul>
    </div>

    <form method="POST" action="{{ route('admin.catalog.save', $type) }}" class="card h-fit space-y-4 p-4 sm:p-5">
        @csrf
        <div>
            <h2 class="font-display text-lg font-bold">Ajouter</h2>
            <p class="text-sm text-slate-500">Le nouvel élément sera immédiatement disponible.</p>
        </div>
        <div><label class="label" for="name">Nom</label><input id="name" name="name" class="input" placeholder="Ex. : Support premium" required></div>
        @if ($color)
            <div><label class="label" for="color">Couleur</label><input id="color" type="color" name="color" value="#3B82F6" class="h-12 w-full cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700"></div>
        @else
            <div><label class="label" for="description">Description</label><textarea id="description" name="description" rows="3" class="input" placeholder="Rôle de ce département…"></textarea></div>
        @endif
        <button class="btn-primary w-full"><x-icon n="plus" class="h-4 w-4"/>Ajouter</button>
    </form>
</div>
@endsection
