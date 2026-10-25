<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function index(Request $request)
    {
        $q = Ticket::visibleTo($request->user())->with(['customer', 'assignee', 'category']);
        foreach (['status', 'priority', 'assignee_id'] as $f) {
            if ($request->filled($f)) {
                $q->where($f, $request->query($f));
            }
        }
        if ($term = $request->query('q')) {
            $q->where(fn ($w) => $w->where('subject', 'like', "%$term%")->orWhere('reference', 'like', "%$term%"));
        }

        return TicketResource::collection($q->latest()->paginate(15));
    }

    public function show(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        return new TicketResource($ticket->load(['customer', 'assignee', 'category', 'replies.user']));
    }

    public function store(StoreTicketRequest $request)
    {
        abort_if($request->user()->isStaff(), 403, 'Seuls les clients peuvent créer des tickets.');
        $t = $this->service->create($request->user(), $request->validated(), $request->file('files', []));

        return (new TicketResource($t->load(['customer', 'category'])))->response()->setStatusCode(201);
    }

    public function reply(ReplyRequest $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $user = $request->user();
        if (! $user->isStaff()) {
            abort_if($ticket->status === TicketStatus::Closed, 422, 'Ce ticket est fermé.');
            abort_if(
                $ticket->status === TicketStatus::Resolved
                && $ticket->resolved_at
                && $ticket->resolved_at->lt(now()->subDays(14)),
                422,
                'Ce ticket ne peut plus être rouvert après 14 jours.',
            );
        }
        $d = $request->validated();
        $internal = $user->isStaff() && (bool) ($d['is_internal'] ?? false);
        $this->service->reply($ticket, $user, $d['body'], $internal, $request->file('files', []));

        return new TicketResource($ticket->refresh()->load(['customer', 'assignee', 'category', 'replies.user']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $d = $request->validated();
        if ($request->boolean('tags_present') && ! array_key_exists('tag_ids', $d)) {
            $d['tag_ids'] = [];
        }
        unset($d['tags_present']);

        return new TicketResource($this->service->updateProps($ticket, $request->user(), $d)->load(['customer', 'assignee', 'category']));
    }

    public function stats(Request $request)
    {
        abort_unless($request->user()->isStaff(), 403);
        $q = fn () => Ticket::visibleTo($request->user());

        return response()->json(['open' => $q()->open()->count(), 'unassigned' => $q()->open()->whereNull('assignee_id')->count(), 'sla_breached' => $q()->open()->where('sla_breached', true)->count(), 'total' => $q()->count()]);
    }
}
