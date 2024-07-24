<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Venue extends Model implements HasMedia
{
    use BelongsToScheduledConference, Cachable, HasFactory, InteractsWithMedia;

    protected $fillable = ['name', 'location', 'description', 'scheduled_conference_id'];


    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('small')
            ->keepOriginalImageFormat()
            ->width(50);

        $this->addMediaConversion('thumb')
            ->keepOriginalImageFormat()
            ->width(400);

        $this->addMediaConversion('thumb-xl')
            ->keepOriginalImageFormat()
            ->width(800);
    }
}
