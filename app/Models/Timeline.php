<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'date',
        'roles',
        'scheduled_conference_id',
    ];

    protected $casts = [
        'roles' => 'array',
        'date' => 'datetime',
    ];
}
