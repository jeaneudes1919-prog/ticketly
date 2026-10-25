<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    protected $guarded = [];

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
