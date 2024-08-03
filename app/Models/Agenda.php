<?php

namespace App\Models;

use App\Facades\Setting;
use Carbon\Carbon;
use App\Models\Timeline;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\Casts\Attribute;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agenda extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    protected function timeSpan(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::squish(
                Carbon::parse($this->time_start)->format(Setting::get('format_time')) . 
                ' - ' . 
                Carbon::parse($this->time_end)->format(Setting::get('format_time'))
            ),
        );
    }

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }
}
