<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class SubmissionFileType extends Model
{
    use HasFactory, SortableTrait, BelongsToScheduledConference;

    protected $fillable = [
        'name',
        'scheduled_conference_id',
    ];
}
