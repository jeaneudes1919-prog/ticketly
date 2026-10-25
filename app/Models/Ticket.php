<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_warned_at' => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest('id');
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]);
    }

    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        if ($u->role === Role::Admin) {
            return $q;
        }
        if ($u->role === Role::Customer) {
            return $q->where('customer_id', $u->id);
        }

        return $q->where(function ($w) use ($u) {
            $w->whereNull('department_id')
                ->orWhereIn('department_id', $u->departments()->pluck('departments.id'))
                ->orWhere('assignee_id', $u->id);
        });
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }
}
