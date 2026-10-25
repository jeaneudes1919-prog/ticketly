@extends('layouts.app')
@section('title', 'Paramètres')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-600">Administration</p>
        <h1 class="mt-1 font-display text-3xl font-bold">Paramètres</h1>
        <p class="text-slate-500">Personnalisez l’identité de votre espace d’assistance.</p>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
    <form method="POST" action="{{ route('admin.settings.save') }}" class="card space-y-5 p-5 sm:p-6">
        @csrf
        <div>
            <label class="label" for="company_name">Nom de l’entreprise</label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $companyName) }}" class="input" required>
            @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="accent_color">Couleur d’accent</label>
            <div class="flex items-center gap-3">
                <input id="accent_color" type="color" name="accent_color" value="{{ old('accent_color', $accentColor) }}" class="h-12 w-20 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700">
                <p class="text-sm text-slate-500">Utilisée pour le logo, les boutons principaux et les accents.</p>
            </div>
            @error('accent_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
            <p class="font-semibold text-slate-700 dark:text-slate-200">Aperçu</p>
            <div class="mt-3 flex items-center gap-3"><span class="brand-gradient grid h-10 w-10 place-items-center rounded-xl text-white"><x-icon n="ticket" class="h-5 w-5"/></span><span class="font-display text-xl font-bold">{{ $companyName }}</span></div>
        </div>
        <button class="btn-primary"><x-icon n="check" class="h-4 w-4"/>Enregistrer les paramètres</button>
    </form>

    <aside class="space-y-4">
        <a href="{{ route('admin.sla') }}" class="card flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-lg"><span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><x-icon n="clock"/></span><div><p class="font-semibold">Politiques SLA</p><p class="text-sm text-slate-500">Délais de première réponse et de résolution.</p></div></a>
        <a href="{{ route('admin.canned') }}" class="card flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-lg"><span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><x-icon n="message"/></span><div><p class="font-semibold">Réponses prédéfinies</p><p class="text-sm text-slate-500">Modèles et raccourcis pour les agents.</p></div></a>
        <a href="{{ route('admin.users') }}" class="card flex items-center gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-lg"><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><x-icon n="users"/></span><div><p class="font-semibold">Utilisateurs</p><p class="text-sm text-slate-500">Rôles, activation et départements.</p></div></a>
    </aside>
</div>
@endsection
