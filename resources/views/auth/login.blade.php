@extends('layouts.guest')
@section('title', 'Connexion')
@section('content')
<div x-data="{ email: @js(old('email', '')), pass: '' }" class="fade-up">
    <h1 class="font-display text-3xl sm:text-4xl font-bold">Bon retour !</h1>
    <p class="mt-2 text-slate-500">Connectez-vous pour accéder à votre espace.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div><label class="label" for="email">Email</label><input id="email" name="email" type="email" x-model="email" class="input" required autofocus autocomplete="email">@error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label class="label" for="password">Mot de passe</label><input id="password" name="password" type="password" x-model="pass" class="input" required autocomplete="current-password">@error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="flex items-center justify-between gap-3"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600">Se souvenir de moi</label><a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-600">Mot de passe oublié ?</a></div>
        <button class="btn-primary w-full !py-3">Se connecter</button>
    </form>
    <div class="mt-6 rounded-2xl bg-slate-50 p-4 dark:bg-slate-900">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Comptes de démonstration</p>
        <div class="flex flex-wrap gap-2">
            @foreach ([['admin@ticketly.dev', 'Admin'], ['agent@ticketly.dev', 'Agent'], ['client@ticketly.dev', 'Client']] as [$e, $l])
                <button type="button" @click="email = '{{ $e }}'; pass = 'Ticketly2026!'" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold transition hover:border-blue-500 dark:border-slate-700 dark:bg-slate-800">{{ $l }}</button>
            @endforeach
        </div>
    </div>
    <p class="mt-6 text-center text-sm text-slate-500">Pas encore de compte ? <a href="{{ route('register') }}" class="font-semibold text-blue-600">Créer un compte client</a></p>
</div>
@endsection
