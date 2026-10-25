@extends('layouts.app')
@section('title', 'Nouveau ticket')
@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('portal') }}" class="text-sm text-slate-500 hover:text-blue-600">← Mes tickets</a>
    <h1 class="mt-2 font-display text-2xl sm:text-3xl font-bold">Nouveau ticket</h1>
    <p class="text-slate-500">Décrivez votre demande avec le plus de détails possible.</p>
    <form method="POST" action="{{ route('portal.store') }}" enctype="multipart/form-data" class="card fade-up mt-6 space-y-4 p-4 sm:space-y-5 sm:p-7">
        @csrf
        <div><label class="label" for="subject">Sujet</label><input id="subject" name="subject" value="{{ old('subject') }}" class="input" placeholder="Ex. : Impossible de me connecter" required>@error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label class="label" for="category_id">Catégorie</label><select id="category_id" name="category_id" class="input"><option value="">Choisir…</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div x-data="{ p: '{{ old('priority', 'medium') }}' }"><label class="label">Priorité</label><input type="hidden" name="priority" :value="p"><div class="grid grid-cols-4 gap-1.5">@foreach (\App\Enums\TicketPriority::cases() as $p)<button type="button" @click="p = '{{ $p->value }}'" :class="p === '{{ $p->value }}' ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-500/15' : 'border-slate-200 dark:border-slate-700'" class="rounded-xl border-2 px-1 py-2 text-xs font-semibold transition">{{ $p->label() }}</button>@endforeach</div></div>
        </div>
        <div><label class="label" for="description">Description</label><textarea id="description" name="description" rows="7" class="input" placeholder="Que s'est-il passé ? Depuis quand ? Quelles étapes avez-vous essayées ?" required>{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        @include('partials.dropzone')
        <div class="flex justify-end gap-3"><a href="{{ route('portal') }}" class="btn-ghost">Annuler</a><button class="btn-primary"><x-icon n="send" class="h-4 w-4"/>Envoyer le ticket</button></div>
    </form>
</div>
@endsection
