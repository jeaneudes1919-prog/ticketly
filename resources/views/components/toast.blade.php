@php
    $toasts = collect(['success' => session('success'), 'error' => session('error'), 'info' => session('info')])->filter();
    if ($errors->any() && ! $toasts->has('error')) { $toasts->put('error', 'Veuillez corriger les erreurs du formulaire.'); }
    $style = ['success' => ['border-emerald-500', 'text-emerald-500', 'Succès'], 'error' => ['border-red-500', 'text-red-500', 'Erreur'], 'info' => ['border-blue-500', 'text-blue-500', 'Information']];
@endphp
<div class="pointer-events-none fixed inset-x-3 bottom-4 z-[100] flex flex-col gap-2 sm:inset-x-auto sm:bottom-auto sm:right-5 sm:top-20 sm:w-96">
    @foreach ($toasts as $type => $msg)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5200)" x-show="show" x-transition:enter="transition duration-300" x-transition:enter-start="translate-y-4 opacity-0" x-transition:leave="transition duration-300" x-transition:leave-end="translate-x-8 opacity-0"
             class="pointer-events-auto relative overflow-hidden rounded-2xl border border-l-4 {{ $style[$type][0] }} border-slate-200 bg-white p-4 pr-10 shadow-2xl dark:border-slate-700 dark:bg-slate-900" role="alert">
            <div class="flex gap-3">
                <x-icon :n="$type === 'error' ? 'x' : ($type === 'success' ? 'check' : 'bell')" class="mt-0.5 h-5 w-5 shrink-0 {{ $style[$type][1] }}"/>
                <div><p class="text-sm font-semibold">{{ $style[$type][2] }}</p><p class="text-sm text-slate-600 dark:text-slate-300">{{ $msg }}</p></div>
            </div>
            <button type="button" @click="show = false" class="absolute right-3 top-3 text-slate-400 hover:text-slate-700" aria-label="Fermer"><x-icon n="x" class="h-4 w-4"/></button>
            <span class="absolute bottom-0 left-0 h-1 bg-current {{ $style[$type][1] }}" style="animation: shrink 5.2s linear forwards"></span>
        </div>
    @endforeach
</div>
<style>@keyframes shrink { from { width: 100%; } to { width: 0; } }</style>
