<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Metable\Metable;

class Track extends Model
{
    use BelongsToScheduledConference, Metable, Cachable;

    protected $fillable = [
        'title',
        'abbreviation',
        'scheduled_conference_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Track $track) {
            if($track->submissions()->exists()) {
                abort(403, 'Before this track can be deleted, you must move paper submitted to it into other track');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
