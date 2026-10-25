<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ \App\Models\Setting::get('company_name', config('app.name', 'Ticketly')) }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $company = \App\Models\Setting::get('company_name', config('app.name', 'Ticketly'));
    $accent = \App\Models\Setting::get('accent_color', '#3B82F6');
@endphp
<body class="min-h-screen bg-white font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200" style="--accent: {{ $accent }}"
      x-data="{ dark: document.documentElement.classList.contains('dark') }"
      x-effect="document.documentElement.classList.toggle('dark', dark); localStorage.theme = dark ? 'dark' : 'light'">
    <button type="button" class="fixed right-5 top-5 z-20 grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white/80 text-slate-500 shadow-sm backdrop-blur transition hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-300" @click="dark = ! dark" aria-label="Changer le thème">
        <span x-show="! dark"><x-icon n="moon"/></span><span x-show="dark" x-cloak><x-icon n="sun"/></span>
    </button>
    <div class="grid min-h-screen lg:grid-cols-[1.1fr_1fr]">
        <section class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <span class="blob -left-20 -top-20 h-96 w-96 bg-blue-600"></span><span class="blob -bottom-24 right-0 h-96 w-96 bg-violet-600" style="animation-delay:-6s"></span><span class="blob left-1/3 top-1/2 h-72 w-72 bg-pink-500/70" style="animation-delay:-11s"></span>
            <a href="/" class="relative flex items-center gap-2.5 font-display text-2xl font-bold"><span class="brand-gradient grid h-10 w-10 place-items-center rounded-xl text-white"><x-icon n="ticket"/></span>{{ $company }}</a>
            <div class="relative">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/80 backdrop-blur"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>Support client en temps réel</p>
                <h2 class="font-display text-5xl font-bold leading-tight">Un support client<br>qui <span class="bg-gradient-to-r from-sky-300 to-pink-300 bg-clip-text text-transparent">tient ses promesses.</span></h2>
                <div class="relative mt-8 h-24 text-lg text-white/80" x-data="{ i: 0, items: ['SLA suivis en temps réel', 'Réponses prédéfinies et notes internes', 'Tableaux de bord et rapports clairs'] }" x-init="setInterval(() => i = (i + 1) % items.length, 2800)">
                    <template x-for="(item, index) in items" :key="item">
                        <div x-show="i === index" x-transition.opacity.duration.500ms class="absolute inset-x-0 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-white/15"><x-icon n="check" class="h-4 w-4"/></span><span x-text="item"></span>
                        </div>
                    </template>
                </div>
            </div>
            <div class="relative flex items-center gap-6 text-sm text-white/50">
                <span class="flex items-center gap-1.5"><x-icon n="shield" class="h-4 w-4"/>Données protégées</span>
                <span class="flex items-center gap-1.5"><x-icon n="clock" class="h-4 w-4"/>Réponse sous SLA</span>
            </div>
        </section>
        <section class="flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <a href="/" class="mb-8 flex items-center gap-2.5 font-display text-2xl font-bold lg:hidden"><span class="brand-gradient grid h-10 w-10 place-items-center rounded-xl text-white"><x-icon n="ticket"/></span>{{ $company }}</a>
                @yield('content')
            </div>
        </section>
    </div>
    <x-toast/>
</body>
</html>
