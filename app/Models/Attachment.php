<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $guarded = [];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function humanSize(): string
    {
        return $this->size > 1048576 ? round($this->size / 1048576, 1).' Mo' : max(1, round($this->size / 1024)).' Ko';
    }
}
