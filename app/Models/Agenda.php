<?php

namespace App\Models;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agenda extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }
}
