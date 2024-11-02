<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenu extends Model
{
    use BelongsToConference, BelongsToScheduledConference, Cachable, HasFactory;

    protected $fillable = [
        'name',
        'handle',
        'conference_id',
        'scheduled_conference_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NavigationMenuItem::class);
    }
}
