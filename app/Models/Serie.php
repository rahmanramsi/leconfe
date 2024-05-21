<?php

namespace App\Models;

use Plank\Metable\Metable;
use Spatie\MediaLibrary\HasMedia;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasAvatar;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Concerns\BelongsToConference;
use App\Models\Enums\SerieType;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Vite;

class Serie extends Model implements HasMedia, HasAvatar, HasName
{
    use Cachable, BelongsToConference, HasFactory, InteractsWithMedia, Metable, SoftDeletes;

    protected $fillable = [
        'conference_id',
        'path',
        'title',
        'description',
        'issn',
        'date_start',
        'date_end',
        'active',
        'type',
    ];

    protected $casts = [
        'active' => 'boolean',
        'date_start' => 'date',
        'date_end' => 'date',
        'type' => SerieType::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function conference() : BelongsTo
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

    public function speakers() : HasMany
    {
        return $this->hasMany(Speaker::class);
    }

    public function speakerRoles() : HasMany
    {
        return $this->hasMany(SpeakerRole::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    public function getPanelUrl(): string
    {
        return route('filament.series.pages.dashboard', ['serie' => $this->path]);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('logo', 'tenant');
    }

    public function getFilamentName(): string
    {
        return $this->title;
    }

    public function getThumbnailUrl(): string
    {
        return $this->getFirstMedia('thumbnail')?->getAvailableUrl(['thumb', 'thumb-xl']) ?? Vite::asset('resources/assets/images/placeholder-vertical.jpg');
    }

    public function getHomeUrl(): string
    {
        return $this->active ? route('livewirePageGroup.conference.pages.home', ['conference' => $this->conference]) : '#';
    }
}
