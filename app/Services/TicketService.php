<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Events\TicketStatusChanged;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketNotice;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(private SlaService $sla) {}

    public function create(User $customer, array $d, array $files = []): Ticket
    {
        $ticket = DB::transaction(function () use ($customer, $d, $files) {
            $ticket = Ticket::create([
                'subject' => $d['subject'],
                'description' => $d['description'],
                'priority' => $d['priority'],
                'category_id' => $d['category_id'] ?? null,
                'department_id' => $d['department_id'] ?? null,
                'customer_id' => $customer->id,
                'status' => TicketStatus::Open,
            ]);
            $ticket->update([
                'reference' => 'TCK-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT),
                'due_at' => $this->sla->resolutionDue($ticket),
            ]);
            $this->attach($ticket, null, $customer, $files);
            $this->log($ticket, $customer, 'created');

            return $ticket;
        });

        TicketCreated::dispatch($ticket, $customer);

        return $ticket;
    }

    public function reply(Ticket $ticket, User $user, string $body, bool $internal = false, array $files = []): TicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $user, $body, $internal, $files) {
            $statusBefore = $ticket->status;
            $assigneeBefore = $ticket->assignee_id;
            $reply = $ticket->replies()->create([
                'user_id' => $user->id,
                'body' => $body,
                'is_internal' => $internal,
            ]);
            $this->attach($ticket, $reply, $user, $files);

            if ($user->isStaff()) {
                if (! $internal) {
                    $ticket->first_response_at ??= now();
                    if (in_array($ticket->status, [TicketStatus::Open, TicketStatus::InProgress], true)) {
                        $ticket->status = TicketStatus::Pending;
                    }
                    if (! $ticket->assignee_id && $user->role === Role::Agent) {
                        $ticket->assignee_id = $user->id;
                    }
                }
            } elseif (in_array($ticket->status, [TicketStatus::Pending, TicketStatus::Resolved], true)) {
                $ticket->status = TicketStatus::Open;
                $ticket->resolved_at = null;
                $ticket->closed_at = null;
            }
            $ticket->save();

            $this->log($ticket, $user, $internal ? 'internal_note' : 'replied');
            if ($statusBefore !== $ticket->status) {
                $this->log($ticket, $user, 'status_changed', ['from' => $statusBefore->value, 'to' => $ticket->status->value]);
            }
            if ($assigneeBefore !== $ticket->assignee_id) {
                $this->log($ticket, $user, 'assignee_id_changed', ['from' => $assigneeBefore, 'to' => $ticket->assignee_id]);
            }

            return $reply;
        });

        TicketReplied::dispatch($ticket, $reply, $user, $internal);

        return $reply;
    }

    public function updateProps(Ticket $ticket, User $by, array $d): Ticket
    {
        $oldStatus = $ticket->status;
        $oldAssignee = $ticket->assignee_id;
        $oldTags = $ticket->tags()->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($ticket, $by, $d, $oldTags) {
            foreach (['status', 'priority', 'assignee_id', 'category_id', 'department_id'] as $key) {
                if (! array_key_exists($key, $d)) {
                    continue;
                }
                $old = $ticket->{$key};
                $oldValue = $old instanceof \BackedEnum ? $old->value : $old;
                $newValue = $d[$key] === '' ? null : $d[$key];
                if ((string) $oldValue === (string) $newValue) {
                    continue;
                }
                $ticket->{$key} = $newValue;
                $this->log($ticket, $by, $key.'_changed', ['from' => $oldValue, 'to' => $newValue]);
                if ($key === 'status') {
                    $this->stampStatus($ticket);
                }
                if ($key === 'priority') {
                    $ticket->due_at = $this->sla->resolutionDue($ticket);
                    $ticket->sla_warned_at = null;
                }
            }

            if (array_key_exists('tag_ids', $d)) {
                $newTags = collect($d['tag_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();
                $added = array_values(array_diff($newTags, $oldTags));
                $removed = array_values(array_diff($oldTags, $newTags));
                $ticket->tags()->sync($newTags);
                if ($added || $removed) {
                    $this->log($ticket, $by, 'tags_changed', ['added' => $added, 'removed' => $removed]);
                }
            }

            $ticket->save();
        });

        $ticket->refresh();
        $assignee = $ticket->assignee_id ? User::find($ticket->assignee_id) : null;
        if ($oldAssignee !== $ticket->assignee_id && $assignee) {
            TicketAssigned::dispatch($ticket, $assignee, $by);
        }
        if ($oldStatus !== $ticket->status) {
            TicketStatusChanged::dispatch($ticket, $oldStatus, $ticket->status, $by);
        }

        return $ticket;
    }

    public function close(Ticket $ticket, User $user): void
    {
        $old = $ticket->status;
        $ticket->status = TicketStatus::Closed;
        $this->stampStatus($ticket);
        $ticket->save();
        $this->log($ticket, $user, 'closed_by_customer');
        TicketStatusChanged::dispatch($ticket, $old, $ticket->status, $user);
    }

    public function reopen(Ticket $ticket, User $user): void
    {
        $old = $ticket->status;
        $ticket->status = TicketStatus::Open;
        $this->stampStatus($ticket);
        $ticket->save();
        $this->log($ticket, $user, 'reopened');
        TicketStatusChanged::dispatch($ticket, $old, $ticket->status, $user);
    }

    public function rate(Ticket $ticket, User $user, int $score, ?string $comment): void
    {
        $ticket->update(['satisfaction' => $score, 'satisfaction_comment' => $comment]);
        $this->log($ticket, $user, 'rated', ['score' => $score]);
    }

    public function merge(Ticket $source, Ticket $target, User $by): Ticket
    {
        DB::transaction(function () use ($source, $target, $by) {
            $source->replies()->update(['ticket_id' => $target->id]);
            Attachment::where('ticket_id', $source->id)->update(['ticket_id' => $target->id]);

            $target->tags()->syncWithoutDetaching($source->tags()->pluck('tags.id')->all());
            $source->tags()->detach();

            if (! $target->first_response_at && $source->first_response_at) {
                $target->first_response_at = $source->first_response_at;
            }

            $this->log($target, $by, 'merged_from', ['ticket_id' => $source->id, 'reference' => $source->reference]);
            $this->log($source, $by, 'merged_into', ['ticket_id' => $target->id, 'reference' => $target->reference]);

            $source->status = TicketStatus::Closed;
            $this->stampStatus($source);
            $source->save();
            $target->save();
        });

        return $target->refresh();
    }

    public function stampStatus(Ticket $ticket): void
    {
        if ($ticket->status === TicketStatus::Resolved) {
            $ticket->resolved_at ??= now();
            $ticket->closed_at = null;
        } elseif ($ticket->status === TicketStatus::Closed) {
            $ticket->resolved_at ??= now();
            $ticket->closed_at = now();
        } else {
            $ticket->resolved_at = null;
            $ticket->closed_at = null;
        }
    }

    public function log(Ticket $ticket, ?User $user, string $action, array $meta = []): void
    {
        ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'action' => $action,
            'meta' => $meta ?: null,
        ]);
    }

    public function staffFor(Ticket $ticket)
    {
        $admins = User::where('role', Role::Admin->value)->where('is_active', true)->get();
        $agents = User::where('role', Role::Agent->value)->where('is_active', true);
        if ($ticket->department_id) {
            $agents->whereHas('departments', fn ($query) => $query->where('departments.id', $ticket->department_id));
        }

        return $admins->concat($agents->get());
    }

    public function notify($users, Ticket $ticket, string $title, string $body): void
    {
        $users = $users instanceof User ? [$users] : $users;
        foreach (collect($users)->filter()->unique('id') as $user) {
            $user->notify(new TicketNotice(
                $title,
                (string) $body,
                $user->isStaff() ? route('tickets.show', $ticket) : route('portal.show', $ticket),
            ));
        }
    }

    private function attach(Ticket $ticket, ?TicketReply $reply, User $user, array $files): void
    {
        foreach ($files as $file) {
            Attachment::create([
                'ticket_id' => $ticket->id,
                'ticket_reply_id' => $reply?->id,
                'user_id' => $user->id,
                'path' => $file->store('attachments', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }
}
