<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\ReplyRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortalController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function index(Request $request)
    {
        $base = Ticket::where('customer_id', $request->user()->id);
        $stats = [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->open()->count(),
            'pending' => (clone $base)->where('status', TicketStatus::Pending->value)->count(),
            'resolved' => (clone $base)->whereIn('status', ['resolved', 'closed'])->count(),
        ];
        $q = (clone $base)->with(['category', 'assignee']);
        if ($s = $request->query('status')) {
            $q->where('status', $s);
        }
        if ($term = $request->query('q')) {
            $q->where(fn ($w) => $w->where('subject', 'like', "%$term%")->orWhere('reference', 'like', "%$term%"));
        }

        return view('portal.index', ['stats' => $stats, 'tickets' => $q->latest()->paginate(8)->withQueryString()]);
    }

    public function create()
    {
        return view('portal.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->service->create($request->user(), $request->validated(), $request->file('files', []));

        return redirect()->route('portal.show', $ticket)->with('success', 'Votre ticket '.$ticket->reference.' a bien été créé.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('customerAct', $ticket);
        $ticket->load(['category', 'assignee', 'attachments', 'replies' => fn ($q) => $q->where('is_internal', false)->with(['user', 'attachments'])]);

        return view('portal.show', compact('ticket'));
    }

    public function reply(ReplyRequest $request, Ticket $ticket)
    {
        Gate::authorize('customerAct', $ticket);
        if ($ticket->status === TicketStatus::Closed) {
            return back()->with('error', 'Ce ticket est fermé. Rouvrez-le pour répondre.');
        }
        if ($ticket->status === TicketStatus::Resolved && $ticket->resolved_at && $ticket->resolved_at->lt(now()->subDays(14))) {
            return back()->with('error', 'Ce ticket ne peut plus être rouvert après 14 jours.');
        }
        $this->service->reply($ticket, $request->user(), $request->validated()['body'], false, $request->file('files', []));

        return back()->with('success', 'Votre réponse a été envoyée.');
    }

    public function close(Ticket $ticket)
    {
        Gate::authorize('customerAct', $ticket);
        $this->service->close($ticket, auth()->user());

        return back()->with('success', 'Le ticket a été fermé.');
    }

    public function reopen(Ticket $ticket)
    {
        Gate::authorize('customerAct', $ticket);
        $limit = $ticket->closed_at ?? $ticket->resolved_at;
        if (! $ticket->isFinal() || ($limit && $limit->lt(now()->subDays(14)))) {
            return back()->with('error', 'Ce ticket ne peut plus être rouvert.');
        }
        $this->service->reopen($ticket, auth()->user());

        return back()->with('success', 'Le ticket a été rouvert.');
    }

    public function rate(Request $request, Ticket $ticket)
    {
        Gate::authorize('customerAct', $ticket);
        abort_unless($ticket->isFinal(), 422, 'Le ticket doit être résolu pour être noté.');
        $d = $request->validate(['score' => 'required|integer|between:1,5', 'comment' => 'nullable|string|max:500']);
        $this->service->rate($ticket, auth()->user(), $d['score'], $d['comment'] ?? null);

        return back()->with('success', 'Merci pour votre évaluation !');
    }
}
