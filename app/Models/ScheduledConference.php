<?php

namespace App\Models;

use Plank\Metable\Metable;
use Spatie\MediaLibrary\HasMedia;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasAvatar;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Concerns\BelongsToConference;
use App\Models\Enums\ScheduledConferenceState;
use App\Models\Enums\ScheduledConferenceType;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Vite;
use Kra8\Snowflake\HasShortflakePrimary;

class ScheduledConference extends Model implements HasMedia, HasAvatar, HasName
{
    use Cachable, BelongsToConference, HasFactory, InteractsWithMedia, Metable, SoftDeletes, HasShortflakePrimary;

    protected $fillable = [
        'conference_id',
        'path',
        'title',
        'date_start',
        'date_end',
        'state',
        'type',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'current' => 'boolean',
        'date_start' => 'date',
        'date_end' => 'date',
        'type' => ScheduledConferenceType::class,
        'state' => ScheduledConferenceState::class,
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (ScheduledConference $scheduledConference) {
            if ($scheduledConference->isDirty('state') && $scheduledConference->state == ScheduledConferenceState::Current) {
                static::query()
                    ->where('conference_id', $scheduledConference->conference_id)
                    ->where('state', ScheduledConferenceState::Current->value)
                    ->where('id', '!=', $scheduledConference->id)
                    ->update(['state' => ScheduledConferenceState::Archived]);
            }
        });
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class);
    }

    public function speakerRoles(): HasMany
    {
        return $this->hasMany(SpeakerRole::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    public function getPanelUrl(): string
    {
        return route('filament.scheduledConference.pages.dashboard', ['serie' => $this->path]);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('logo', 'tenant');
    }

    public function getFilamentName(): string
    {
        return $this->title;
    }

    public function hasThumbnail(): bool
    {
        return $this->getMedia('thumbnail')->isNotEmpty();
    }

    public function getThumbnailUrl(): string
    {
        return $this->getFirstMedia('thumbnail')?->getAvailableUrl(['thumb', 'thumb-xl']) ?? Vite::asset('resources/assets/images/placeholder-vertical.jpg');
    }

    public function getHomeUrl(): string
    {
        return $this->isCurrent()
            ? route('livewirePageGroup.conference.pages.home', ['conference' => $this->conference])
            : route('livewirePageGroup.series.pages.home', ['conference' => $this->conference, 'serie' => $this->path]);
    }

    public function isCurrent(): bool
    {
        return $this->state == ScheduledConferenceState::Current;
    }

    public function isDraft(): bool
    {
        return $this->state == ScheduledConferenceState::Draft;
    }

    public function isPublished(): bool
    {
        return $this->state == ScheduledConferenceState::Published;
    }

    public function isUpcoming(): bool
    {
        return $this->isPublished();
    }

    public function isArchived(): bool
    {
        return $this->state == ScheduledConferenceState::Archived;
    }

    public function scopeType($query, ScheduledConferenceType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeState($query, ScheduledConferenceState $state)
    {
        return $query->where('state', $state);
    }
}