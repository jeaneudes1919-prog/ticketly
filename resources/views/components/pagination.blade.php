@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Page <b class="text-slate-700 dark:text-slate-200">{{ $paginator->currentPage() }}</b> sur {{ $paginator->lastPage() }}
        </p>
        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-ghost cursor-not-allowed opacity-40" aria-disabled="true">← Précédent</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-ghost">← Précédent</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span aria-current="page" class="grid h-10 min-w-10 place-items-center rounded-xl bg-slate-900 px-3 text-sm font-bold text-white dark:bg-white dark:text-slate-900">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="grid h-10 min-w-10 place-items-center rounded-xl px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-ghost">Suivant →</a>
            @else
                <span class="btn-ghost cursor-not-allowed opacity-40" aria-disabled="true">Suivant →</span>
            @endif
        </div>
    </nav>
@endif
