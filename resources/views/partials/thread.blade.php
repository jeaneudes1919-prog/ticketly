@php $initialFiles = $ticket->attachments->whereNull('ticket_reply_id'); @endphp
<div class="space-y-5">
    <div class="flex gap-3">
        <x-avatar :user="$ticket->customer" :size="10"/>
        <div class="min-w-0 flex-1 rounded-2xl rounded-tl-sm border border-slate-200 bg-white p-3.5 dark:border-slate-800 dark:bg-slate-900 sm:p-4">
            <div class="flex flex-wrap items-baseline gap-2"><b class="text-sm">{{ $ticket->customer->name }}</b><span class="text-xs text-slate-400">{{ $ticket->created_at->translatedFormat('d M Y à H:i') }}</span></div>
            <div class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed">{{ $ticket->description }}</div>
            @include('partials.files', ['files' => $initialFiles])
        </div>
    </div>
    @foreach ($ticket->replies as $r)
        @php $mine = $r->user->isStaff(); @endphp
        <div class="flex gap-3 {{ $mine && ! $r->is_internal ? 'flex-row-reverse' : '' }}">
            <x-avatar :user="$r->user" :size="10"/>
            <div class="min-w-0 flex-1 rounded-2xl border p-3.5 sm:max-w-[88%] sm:p-4 {{ $r->is_internal ? 'border-amber-300 bg-amber-50 dark:border-amber-500/40 dark:bg-amber-500/10' : ($mine ? 'rounded-tr-sm border-blue-200 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/10' : 'rounded-tl-sm border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900') }}">
                <div class="flex flex-wrap items-baseline gap-2"><b class="text-sm">{{ $r->user->name }}</b>@if ($r->is_internal)<span class="badge bg-amber-200 text-amber-800">Note interne</span>@elseif ($mine)<span class="badge bg-blue-100 text-blue-700">Équipe</span>@endif<span class="text-xs text-slate-400">{{ $r->created_at->diffForHumans() }}</span></div>
                <div class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed">{{ $r->body }}</div>
                @include('partials.files', ['files' => $r->attachments])
            </div>
        </div>
    @endforeach
</div>
