<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class SubmissionFileType extends Model
{
    use Cachable, HasFactory, SortableTrait, BelongsToScheduledConference;

    protected $fillable = [
        'name',
        'scheduled_conference_id',
    ];
}
