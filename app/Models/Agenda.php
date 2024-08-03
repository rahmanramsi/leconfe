<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }
}
