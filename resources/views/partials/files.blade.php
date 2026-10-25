@if ($files->count())
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach ($files as $f)
            <a href="{{ route('attachments.download', $f) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium transition hover:border-blue-500 dark:border-slate-700 dark:bg-slate-800"><x-icon n="clip" class="h-3.5 w-3.5"/>{{ $f->original_name }} <span class="text-slate-400">({{ $f->humanSize() }})</span></a>
        @endforeach
    </div>
@endif
