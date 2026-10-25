<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Http\Requests\BulkTicketRequest;
use App\Http\Requests\MergeTicketRequest;
use App\Http\Requests\ReplyRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Attachment;
use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $view = $request->query('view', 'all');
        $query = Ticket::visibleTo($user)->with(['customer', 'assignee', 'category', 'tags']);

        match ($view) {
            'mine' => $query->where('assignee_id', $user->id)->open(),
            'unassigned' => $query->whereNull('assignee_id')->open(),
            'late' => $query->where('sla_breached', true)->open(),
            'resolved' => $query->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]),
            default => null,
        };

        foreach (['status', 'priority', 'category_id', 'assignee_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($tags) => $tags->where('tags.id', $request->query('tag')));
        }
        if ($request->filled('period') && in_array((int) $request->query('period'), [7, 30, 90], true)) {
            $query->where('created_at', '>=', now()->subDays((int) $request->query('period')));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }
        if ($term = $request->query('q')) {
            $query->where(fn ($where) => $where
                ->where('subject', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$term}%")));
        }

        match ($request->query('sort')) {
            'recent' => $query->latest(),
            'oldest' => $query->oldest(),
            default => $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")->orderBy('due_at'),
        };

        $counts = [
            'all' => Ticket::visibleTo($user)->count(),
            'mine' => Ticket::visibleTo($user)->where('assignee_id', $user->id)->open()->count(),
            'unassigned' => Ticket::visibleTo($user)->whereNull('assignee_id')->open()->count(),
            'late' => Ticket::visibleTo($user)->where('sla_breached', true)->open()->count(),
            'resolved' => Ticket::visibleTo($user)->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count(),
        ];

        return view('tickets.index', [
            'tickets' => $query->paginate(12)->withQueryString(),
            'view' => $view,
            'counts' => $counts,
            'categories' => Category::orderBy('name')->get(),
            'agents' => $this->agents(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);
        $ticket->load(['customer', 'assignee', 'category', 'department', 'tags', 'attachments', 'replies.user', 'replies.attachments', 'logs.user']);

        $canned = CannedResponse::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->orderBy('title')
            ->get(['title', 'body', 'shortcut']);

        $history = Ticket::visibleTo(auth()->user())
            ->where('customer_id', $ticket->customer_id)
            ->where('id', '!=', $ticket->id)
            ->latest()
            ->take(5)
            ->get();

        $mergeTargets = Ticket::visibleTo(auth()->user())
            ->where('customer_id', $ticket->customer_id)
            ->where('id', '!=', $ticket->id)
            ->open()
            ->latest()
            ->take(15)
            ->get(['id', 'reference', 'subject']);

        return view('tickets.show', [
            'ticket' => $ticket,
            'agents' => $this->agents(),
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'canned' => $canned,
            'history' => $history,
            'mergeTargets' => $mergeTargets,
        ]);
    }

    public function reply(ReplyRequest $request, Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);
        $data = $request->validated();
        $this->service->reply(
            $ticket,
            $request->user(),
            $data['body'],
            (bool) ($data['is_internal'] ?? false),
            $request->file('files', []),
        );

        return back()->with('success', ($data['is_internal'] ?? false) ? 'Note interne ajoutée.' : 'Réponse envoyée au client.');
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();
        if ($request->boolean('tags_present') && ! array_key_exists('tag_ids', $data)) {
            $data['tag_ids'] = [];
        }
        unset($data['tags_present']);
        $this->service->updateProps($ticket, $request->user(), $data);

        return back()->with('success', 'Ticket mis à jour.');
    }

    public function merge(MergeTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);
        $target = $request->target();
        abort_unless($request->user()->can('manage', $target), 403);
        abort_unless($target->customer_id === $ticket->customer_id, 422, 'Les deux tickets doivent appartenir au même client.');
        abort_if($target->isFinal(), 422, 'Le ticket de destination doit être ouvert.');
        $this->service->merge($ticket, $target, $request->user());

        return redirect()->route('tickets.show', $target)->with('success', 'Le ticket '.$ticket->reference.' a été fusionné dans '.$target->reference.'.');
    }

    public function bulk(BulkTicketRequest $request)
    {
        $data = $request->validated();
        if ($data['action'] === 'assign') {
            validator(
                ['value' => $data['value'] ?? null],
                ['value' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->whereIn('role', ['agent', 'admin'])
                    ->where('is_active', true))]],
            )->validate();
        } elseif ($data['action'] === 'status') {
            validator(
                ['value' => $data['value'] ?? null],
                ['value' => ['required', Rule::enum(TicketStatus::class)]],
            )->validate();
        }

        $value = $data['action'] === 'close' ? TicketStatus::Closed->value : $data['value'];
        $updated = 0;
        foreach (Ticket::visibleTo($request->user())->whereIn('id', $data['ids'])->get() as $ticket) {
            if (! $request->user()->can('manage', $ticket)) {
                continue;
            }
            $this->service->updateProps(
                $ticket,
                $request->user(),
                $data['action'] === 'assign' ? ['assignee_id' => $value] : ['status' => $value],
            );
            $updated++;
        }

        return back()->with('success', "{$updated} ticket(s) mis à jour.");
    }

    public function customers(Request $request)
    {
        $query = User::where('role', Role::Customer->value)->withCount('tickets');
        if ($term = $request->query('q')) {
            $query->where(fn ($where) => $where
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"));
        }

        return view('tickets.customers', ['customers' => $query->orderBy('name')->paginate(15)->withQueryString()]);
    }

    public function download(Attachment $attachment)
    {
        $ticket = $attachment->ticket;
        $user = auth()->user();
        abort_unless(
            $user->isStaff() ? Gate::allows('manage', $ticket) : Gate::allows('customerAct', $ticket),
            403,
        );

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    private function agents()
    {
        return User::whereIn('role', [Role::Agent->value, Role::Admin->value])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
