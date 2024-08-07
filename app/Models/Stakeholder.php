<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Stakeholder extends Model implements HasMedia, Sortable
{
    use HasFactory, InteractsWithMedia, BelongsToConference, BelongsToScheduledConference, SortableTrait, Cachable;

    public const TYPE_SPONSOR = 1;
    public const TYPE_PARTNER = 2;

    protected $fillable = [
        'conference_id',
        'scheduled_conference_id',
        'type',
        'level_id',
        'name',
        'description',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    public function scopeSponsors($query)
    {
        return $query->where('type', self::TYPE_SPONSOR);
    }

    public function scopePartners($query)
    {
        return $query->where('type', self::TYPE_PARTNER);
    }

    public function level() : BelongsTo
    {
        return $this->belongsTo(StakeholderLevel::class);
    }

    public function buildSortQuery()
    {
        return static::query()
            ->where('level_id', $this->level_id);
    }

    public function scopeShow($query, $show = true)
    {
        return $query->where('is_shown', $show);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->keepOriginalImageFormat()
            ->width(400);

        $this->addMediaConversion('thumb-xl')
            ->keepOriginalImageFormat()
            ->width(800);
    }
}
