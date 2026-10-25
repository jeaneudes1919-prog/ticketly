<div x-data x-show="$store.confirm.open" x-cloak x-transition.opacity @keydown.escape.window="$store.confirm.open = false"
     class="fixed inset-0 z-[110] grid place-items-center bg-slate-900/50 p-4 backdrop-blur-sm" @mousedown.self="$store.confirm.open = false">
    <div role="alertdialog" aria-modal="true" x-show="$store.confirm.open" x-transition:enter="transition duration-200" x-transition:enter-start="scale-90 opacity-0" class="w-full max-w-sm rounded-3xl bg-white p-7 text-center shadow-2xl dark:bg-slate-900">
        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full" :class="$store.confirm.danger ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'"><x-icon n="shield" class="h-8 w-8"/></div>
        <h2 class="mt-4 font-display text-2xl font-bold" x-text="$store.confirm.title"></h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400" x-text="$store.confirm.message"></p>
        <div class="mt-6 grid grid-cols-2 gap-3">
            <button type="button" class="btn-ghost" @click="$store.confirm.open = false">Annuler</button>
            <button type="button" class="btn text-white" :class="$store.confirm.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'" @click="$store.confirm.accept()" x-text="$store.confirm.label"></button>
        </div>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
