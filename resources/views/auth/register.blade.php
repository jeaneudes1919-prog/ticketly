@extends('layouts.guest')
@section('title', 'Inscription')
@section('content')
<div class="fade-up">
    <h1 class="font-display text-3xl sm:text-4xl font-bold">Créer un compte</h1>
    <p class="mt-2 text-slate-500">Ouvrez des tickets et suivez leur traitement en direct.</p>
    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf
        <div><label class="label" for="name">Nom complet</label><input id="name" name="name" value="{{ old('name') }}" class="input" required>@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label class="label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required>@error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
            <div><label class="label" for="password">Mot de passe</label><input id="password" name="password" type="password" class="input" required>@error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label class="label" for="pc">Confirmation</label><input id="pc" name="password_confirmation" type="password" class="input" required></div>
        </div>
        <button class="btn-primary w-full !py-3">Créer mon compte</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Déjà inscrit ? <a href="{{ route('login') }}" class="font-semibold text-blue-600">Se connecter</a></p>
</div>
@endsection
