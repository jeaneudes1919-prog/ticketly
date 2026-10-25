<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tableau de bord') · {{ \App\Models\Setting::get('company_name', config('app.name', 'Ticketly')) }}</title>
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $u = auth()->user();
    $company = \App\Models\Setting::get('company_name', config('app.name', 'Ticketly'));
    $accent = \App\Models\Setting::get('accent_color', '#3B82F6');
    $nav = $u->isStaff()
        ? [['dashboard', 'Tableau de bord', 'dash'], ['tickets.index', 'Tickets', 'ticket'], ['customers', 'Clients', 'users'], ['reports', 'Rapports', 'chart']]
        : [['portal.tickets', 'Mes tickets', 'ticket'], ['portal.create', 'Nouveau ticket', 'plus']];
    $adminNav = $u->isAdmin() ? [
        ['admin.users', 'Utilisateurs', 'users'],
        ['admin.categories', 'Catégories', 'tag'],
        ['admin.departments', 'Départements', 'building'],
        ['admin.tags', 'Étiquettes', 'tag'],
        ['admin.sla', 'Politiques SLA', 'clock'],
        ['admin.canned', 'Réponses prédéfinies', 'message'],
        ['admin.settings', 'Paramètres', 'cog'],
    ] : [];
    $notifs = $u->unreadNotifications()->latest()->take(8)->get();
    $active = fn (string $route) => request()->routeIs($route)
        || request()->routeIs(str_replace('.index', '.*', $route))
        || ($route === 'portal.tickets' && request()->routeIs('portal.show'));
@endphp
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200" style="--accent: {{ $accent }}"
      x-data="{ side: false, dark: document.documentElement.classList.contains('dark'), collapsed: localStorage.sidebar === 'collapsed' }"
      x-effect="document.documentElement.classList.toggle('dark', dark); localStorage.theme = dark ? 'dark' : 'light'; localStorage.sidebar = collapsed ? 'collapsed' : 'open'">
    <div x-show="side" x-cloak x-transition.opacity @click="side = false" class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-[width,transform] duration-300 dark:border-slate-800 dark:bg-slate-900 lg:translate-x-0"
           :class="[side ? '!translate-x-0' : '', collapsed ? 'lg:w-20' : 'lg:w-64']">
        <div class="flex h-16 items-center gap-2 px-3">
            <a href="/" class="flex min-w-0 items-center gap-2.5 px-2 font-display text-xl font-bold">
                <span class="brand-gradient grid h-9 w-9 shrink-0 place-items-center rounded-xl text-white shadow-lg shadow-blue-600/20"><x-icon n="ticket" class="h-5 w-5"/></span>
                <span class="truncate transition-all duration-200" :class="collapsed ? 'lg:max-w-0 lg:opacity-0' : 'lg:max-w-[12rem]'">{{ $company }}</span>
            </a>
            <button type="button" class="ml-auto hidden h-8 w-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 lg:grid"
                    @click="collapsed = ! collapsed" :aria-label="collapsed ? 'Déplier la navigation' : 'Replier la navigation'">
                <span x-show="!collapsed"><x-icon n="chevron-left" class="h-4 w-4"/></span>
                <span x-show="collapsed" x-cloak><x-icon n="chevron-right" class="h-4 w-4"/></span>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-4">
            @foreach ($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}" class="nav-link {{ $active($route) ? 'nav-link-active' : '' }}"
                   :class="collapsed && 'lg:justify-center lg:px-2'" :title="collapsed ? '{{ $label }}' : null">
                    <x-icon :n="$icon" class="h-5 w-5 shrink-0"/>
                    <span class="truncate transition-all duration-200" :class="collapsed ? 'lg:max-w-0 lg:opacity-0' : 'lg:max-w-[12rem]'">{{ $label }}</span>
                </a>
            @endforeach

            @if ($adminNav)
                <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-widest text-slate-400" :class="collapsed && 'lg:hidden'">Administration</p>
                @foreach ($adminNav as [$route, $label, $icon])
                    <a href="{{ route($route) }}" class="nav-link {{ $active($route) ? 'nav-link-active' : '' }}"
                       :class="collapsed && 'lg:justify-center lg:px-2'" :title="collapsed ? '{{ $label }}' : null">
                        <x-icon :n="$icon" class="h-5 w-5 shrink-0"/>
                        <span class="truncate transition-all duration-200" :class="collapsed ? 'lg:max-w-0 lg:opacity-0' : 'lg:max-w-[12rem]'">{{ $label }}</span>
                    </a>
                @endforeach
            @endif
        </nav>

        <div class="border-t border-slate-200 p-3 dark:border-slate-800">
            <div class="flex items-center gap-3 rounded-xl p-2" :class="collapsed && 'lg:justify-center lg:p-1'">
                <x-avatar :user="$u" :size="9"/>
                <div class="min-w-0 transition-all duration-200" :class="collapsed ? 'lg:hidden' : ''">
                    <p class="truncate text-sm font-semibold">{{ $u->name }}</p>
                    <p class="text-xs text-slate-500">{{ $u->role->label() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="nav-link mt-1 w-full" :class="collapsed && 'lg:justify-center lg:px-2'" :title="collapsed ? 'Déconnexion' : null">
                    <x-icon n="logout" class="h-5 w-5 shrink-0"/><span :class="collapsed ? 'lg:hidden' : ''">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="min-h-screen transition-[padding] duration-300" :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80 sm:px-6">
            <button class="btn-ghost !p-2 lg:hidden" @click="side = true" aria-label="Menu"><x-icon n="menu"/></button>
            <form method="GET" action="{{ $u->isStaff() ? route('tickets.index') : route('portal.tickets') }}" class="relative max-w-md flex-1">
                <span class="absolute left-3 top-2.5 text-slate-400"><x-icon n="search" class="h-5 w-5"/></span>
                <input name="q" value="{{ request('q') }}" class="input !rounded-full !pl-10" placeholder="Rechercher un ticket…" aria-label="Rechercher">
            </form>
            <div class="ml-auto flex items-center gap-1.5">
                <button class="btn-ghost !p-2.5" @click="dark = ! dark" aria-label="Changer le thème">
                    <span x-show="! dark"><x-icon n="moon"/></span><span x-show="dark" x-cloak><x-icon n="sun"/></span>
                </button>
                <div class="relative" x-data="{ o: false }" @click.outside="o = false">
                    <button class="btn-ghost relative !p-2.5" @click="o = ! o" aria-label="Notifications">
                        <x-icon n="bell"/>
                        @if ($notifs->count())<span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $notifs->count() }}</span>@endif
                    </button>
                    <div x-show="o" x-cloak x-transition class="absolute right-0 mt-2 w-80 max-w-[85vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <b class="text-sm">Notifications</b>
                            @if ($notifs->count())
                                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-xs font-semibold text-blue-600">Tout marquer comme lu</button></form>
                            @endif
                        </div>
                        @forelse ($notifs as $n)
                            <a href="{{ route('notifications.open', $n->id) }}" class="block border-b border-slate-50 px-4 py-3 text-sm transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                                <b class="block">{{ $n->data['title'] }}</b>
                                <span class="line-clamp-1 text-slate-500">{{ $n->data['body'] }}</span>
                                <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                            </a>
                        @empty
                            <p class="px-4 py-8 text-center text-sm text-slate-400">Aucune nouvelle notification</p>
                        @endforelse
                    </div>
                </div>
                <div class="relative" x-data="{ u: false }" @click.outside="u = false">
                    <button class="btn-ghost flex items-center gap-2 !p-1.5 !pr-2" @click="u = ! u" aria-label="Menu utilisateur">
                        <x-avatar :user="$u" :size="7"/><span class="hidden text-sm font-semibold sm:block">{{ $u->name }}</span><x-icon n="chevron-down" class="hidden h-3.5 w-3.5 text-slate-400 sm:block"/>
                    </button>
                    <div x-show="u" x-cloak x-transition class="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-100 p-4 dark:border-slate-800">
                            <p class="font-semibold">{{ $u->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                            <span class="badge mt-2 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $u->role->label() }}</span>
                        </div>
                        <a href="{{ $u->isStaff() ? route('dashboard') : route('portal.tickets') }}" class="flex items-center gap-2 px-4 py-3 text-sm transition hover:bg-slate-50 dark:hover:bg-slate-800"><x-icon n="user" class="h-4 w-4"/>Mon espace</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-2 px-4 py-3 text-sm text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10"><x-icon n="logout" class="h-4 w-4"/>Se déconnecter</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">@yield('content')</main>
    </div>
    <x-toast/><x-confirm/>
    @stack('scripts')
</body>
</html>
