@props(['icon' => 'inbox', 'title', 'text' => null])
<div class="mx-auto flex max-w-sm flex-col items-center text-center">
    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <x-icon :n="$icon" class="h-7 w-7"/>
    </span>
    <h3 class="mt-4 font-display text-xl font-bold">{{ $title }}</h3>
    @if ($text)<p class="mt-1 text-sm text-slate-500">{{ $text }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
