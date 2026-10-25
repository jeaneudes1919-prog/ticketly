@extends('layouts.guest')
@section('title', 'Mot de passe oublié')
@section('content')
<div class="fade-up">
    <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-blue-600">← Retour à la connexion</a>
    <h1 class="mt-4 font-display text-4xl font-bold">Mot de passe oublié ?</h1>
    <p class="mt-2 text-slate-500">Indiquez votre adresse email : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full !py-3">Envoyer le lien de réinitialisation</button>
    </form>
    <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
        <b>Démo :</b> l’email est écrit dans <code class="rounded bg-white/60 px-1 dark:bg-slate-900/60">storage/logs/laravel.log</code>. Ouvrez le lien qui y figure pour réinitialiser le mot de passe.
    </div>
</div>
@endsection
