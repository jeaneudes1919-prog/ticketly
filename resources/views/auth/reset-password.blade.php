@extends('layouts.guest')
@section('title', 'Nouveau mot de passe')
@section('content')
<div class="fade-up">
    <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-blue-600">← Retour à la connexion</a>
    <h1 class="mt-4 font-display text-3xl sm:text-4xl font-bold">Nouveau mot de passe</h1>
    <p class="mt-2 text-slate-500">Choisissez un mot de passe solide pour sécuriser votre compte.</p>
    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="input" required autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Nouveau mot de passe</label>
            <input id="password" name="password" type="password" class="input" required autocomplete="new-password">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirmation</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="input" required autocomplete="new-password">
        </div>
        <button class="btn-primary w-full !py-3">Réinitialiser mon mot de passe</button>
    </form>
</div>
@endsection
