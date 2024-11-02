<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CommitteeRole extends Model implements Sortable
{
    use BelongsToScheduledConference, HasFactory, SortableTrait;

    protected $table = 'committee_roles';

    protected $fillable = [
        'scheduled_conference_id',
        'parent_id',
        'name',
    ];

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeByActiveSeries($query, $scheduledConferencesId)
    {
        return $query->where('scheduled_conference_id', $scheduledConferencesId)
            ->whereHas('committees')
            ->with(['committees' => ['media', 'meta']]);
    }
}
