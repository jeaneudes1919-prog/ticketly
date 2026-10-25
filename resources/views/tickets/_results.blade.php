<div class="mb-3 flex flex-wrap items-center justify-between gap-2">
    <p class="text-xs font-semibold uppercase tracking-[.16em] text-slate-400">
        {{ $tickets->total() }} résultat{{ $tickets->total() > 1 ? 's' : '' }}
    </p>
    @if ($tickets->total())
        <p class="text-xs text-slate-400">Page {{ $tickets->currentPage() }} sur {{ $tickets->lastPage() }}</p>
    @endif
</div>

<form method="POST" action="{{ route('tickets.bulk') }}" x-data="{ sel: [], action: 'assign' }">
    @csrf
    <div x-show="sel.length" x-cloak x-transition class="card mb-3 flex flex-wrap items-center gap-3 border-blue-300 bg-blue-50 p-3 dark:border-blue-500/40 dark:bg-blue-500/10">
        <b class="text-sm" x-text="sel.length + ' sélectionné(s)'"></b>
        <select name="action" x-model="action" class="input !w-auto">
            <option value="assign">Assigner à…</option>
            <option value="status">Changer le statut…</option>
            <option value="close">Fermer les tickets</option>
        </select>
        <select x-show="action === 'assign'" :name="action === 'assign' ? 'value' : ''" class="input !w-auto">
            @foreach ($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach
        </select>
        <select x-show="action === 'status'" :name="action === 'status' ? 'value' : ''" class="input !w-auto">
            @foreach (\App\Enums\TicketStatus::cases() as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach
        </select>
        <span x-show="action === 'close'" class="text-sm text-slate-500">Les tickets seront marqués comme fermés.</span>
        <button class="btn-primary">Appliquer</button>
    </div>

    <div class="space-y-3 md:card md:overflow-hidden md:space-y-0">
        <table class="w-full md:divide-y md:divide-slate-100 dark:md:divide-slate-800">
            <thead class="hidden bg-slate-50 md:table-header-group dark:bg-slate-800/50">
                <tr><th class="th w-10"></th><th class="th">Ticket</th><th class="th">Client</th><th class="th">Priorité</th><th class="th">Statut</th><th class="th">Assigné</th><th class="th">SLA</th></tr>
            </thead>
            <tbody class="block md:table-row-group md:divide-y md:divide-slate-100 dark:md:divide-slate-800">
            @forelse ($tickets as $index => $ticket)
                <tr class="fade-up card mb-3 block p-4 transition hover:bg-slate-50 md:mb-0 md:table-row md:rounded-none md:border-0 md:p-0 md:shadow-none dark:hover:bg-slate-800/40" style="--i:{{ $index }}">
                    <td class="block md:table-cell md:px-4 md:py-3"><input type="checkbox" name="ids[]" value="{{ $ticket->id }}" x-model="sel" class="rounded border-slate-300 text-blue-600" aria-label="Sélectionner {{ $ticket->reference }}"></td>
                    <td class="block md:table-cell md:px-4 md:py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="block">
                            <span class="text-xs font-semibold text-blue-600">{{ $ticket->reference }}</span>
                            <span class="block font-semibold hover:text-blue-600">{{ $ticket->subject }}</span>
                            <span class="mt-1 flex flex-wrap gap-1">@foreach ($ticket->tags as $tag)<span class="badge text-white" style="background:{{ $tag->color }}">{{ $tag->name }}</span>@endforeach</span>
                        </a>
                    </td>
                    <td class="block text-sm md:table-cell md:px-4 md:py-3"><span class="text-slate-500 md:hidden">Client : </span>{{ $ticket->customer->name }}</td>
                    <td class="inline-block pr-2 md:table-cell md:px-4 md:py-3"><span class="badge {{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</span></td>
                    <td class="inline-block md:table-cell md:px-4 md:py-3"><span class="badge {{ $ticket->status->color() }}">{{ $ticket->status->label() }}</span></td>
                    <td class="block text-sm md:table-cell md:px-4 md:py-3">
                        @if ($ticket->assignee)
                            <span class="flex items-center gap-2"><x-avatar :user="$ticket->assignee" :size="6"/>{{ $ticket->assignee->name }}</span>
                        @else
                            <span class="text-slate-400">Non assigné</span>
                        @endif
                    </td>
                    <td class="block pt-2 md:table-cell md:px-4 md:py-3 md:pt-3"><x-sla :t="$ticket"/></td>
                </tr>
            @empty
                <tr class="block md:table-row"><td colspan="7" class="block px-6 py-16 text-center md:table-cell">
                    <x-empty-state icon="inbox" title="Aucun ticket ne correspond" text="Modifiez vos filtres pour élargir la recherche.">
                        <a href="{{ route('tickets.index') }}" class="btn-ghost" @click.prevent="reset()">Réinitialiser les filtres</a>
                    </x-empty-state>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="mt-6" data-pagination>{{ $tickets->links('components.pagination') }}</div>
