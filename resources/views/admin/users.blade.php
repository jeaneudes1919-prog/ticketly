@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('content')
<div x-data="{ open: false, f: {} }">
    <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="font-display text-3xl font-bold">Utilisateurs</h1>
        <div class="flex flex-wrap gap-3"><form method="GET"><input name="q" value="{{ request('q') }}" class="input" placeholder="Rechercher"></form>
            <button class="btn-primary" @click="f = { id: '', name: '', email: '', role: 'agent', is_active: true, department_ids: [] }; open = true"><x-icon n="plus" class="h-4 w-4"/>Nouvel utilisateur</button></div></div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($users as $i => $u)
            <div class="card fade-up flex items-center gap-4 p-5 {{ $u->is_active ? '' : 'opacity-60' }}" style="--i:{{ $i }}"><x-avatar :user="$u" :size="12"/>
                <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $u->name }}</p><p class="truncate text-sm text-slate-500">{{ $u->email }}</p>
                    <span class="badge mt-1 {{ $u->role === \App\Enums\Role::Admin ? 'bg-violet-100 text-violet-700' : ($u->role === \App\Enums\Role::Agent ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">{{ $u->role->label() }}</span>@unless ($u->is_active)<span class="badge bg-red-100 text-red-700">Désactivé</span>@endunless</div>
                <button class="btn-ghost !px-3 !py-2" @click="f = @js(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role->value, 'is_active' => $u->is_active, 'department_ids' => $u->departments->pluck('id')]); open = true">Modifier</button></div>
        @endforeach
    </div>
    <div class="mt-6">{{ $users->links('components.pagination') }}</div>
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm" @keydown.escape.window="open = false" @mousedown.self="open = false">
        <form method="POST" action="{{ route('admin.users.save') }}" class="card w-full max-w-lg space-y-4 p-6" x-show="open" x-transition>
            @csrf<input type="hidden" name="id" :value="f.id">
            <div class="flex items-center justify-between"><h2 class="font-display text-xl font-bold" x-text="f.id ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'"></h2><button type="button" @click="open = false" aria-label="Fermer"><x-icon n="x"/></button></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="label">Nom</label><input name="name" x-model="f.name" class="input" required></div><div><label class="label">Email</label><input type="email" name="email" x-model="f.email" class="input" required></div></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="label">Rôle</label><select name="role" x-model="f.role" class="input"><option value="admin">Administrateur</option><option value="agent">Agent</option><option value="customer">Client</option></select></div><div><label class="label" x-text="f.id ? 'Nouveau mot de passe (facultatif)' : 'Mot de passe'"></label><input type="password" name="password" class="input" :required="! f.id" minlength="8"></div></div>
            <div x-show="f.role === 'agent'"><label class="label">Départements</label><div class="flex flex-wrap gap-2">@foreach ($departments as $d)<label class="flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-sm dark:border-slate-700"><input type="checkbox" name="department_ids[]" value="{{ $d->id }}" :checked="(f.department_ids || []).includes({{ $d->id }})" class="rounded border-slate-300 text-blue-600">{{ $d->name }}</label>@endforeach</div></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" x-model="f.is_active" class="rounded border-slate-300 text-blue-600">Compte actif</label>
            <div class="flex justify-end gap-3"><button type="button" class="btn-ghost" @click="open = false">Annuler</button><button class="btn-primary">Enregistrer</button></div>
        </form>
    </div>
</div>
@endsection
