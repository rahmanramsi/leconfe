<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use Database\Factories\SpeakerFactory;
use Filament\Models\Contracts\HasAvatar;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Plank\Metable\Metable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Speaker extends Model implements HasAvatar, HasMedia, Sortable
{
    use BelongsToScheduledConference, Cachable, HasFactory, InteractsWithMedia, Metable, Notifiable, SortableTrait;

    protected $table = 'speakers';

    protected $fillable = [
        'speaker_role_id',
        'email',
        'given_name',
        'family_name',
        'public_name',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($publicName = $this->getMeta('public_name')) {
                    return $publicName;
                }

                return Str::squish($this->given_name.' '.$this->family_name);
            },
        );
    }

    protected static function newFactory(): Factory
    {
        return SpeakerFactory::new();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar')
            ->keepOriginalImageFormat()
            ->width(50);

        $this->addMediaConversion('thumb')
            ->keepOriginalImageFormat()
            ->width(400);

        $this->addMediaConversion('thumb-xl')
            ->keepOriginalImageFormat()
            ->width(800);
    }

    public function scopeEmail(Builder $query, string $email)
    {
        return $query->where('email', $email);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($profilePicture = $this->getFirstMedia('profile')?->getAvailableUrl(['thumb', 'thumb-xl'])) {
            return $profilePicture;
        }

        $name = Str::of($this->fullName)
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background=111827&font-size=0.33';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SpeakerRole::class, 'speaker_role_id', 'id');
    }
}
