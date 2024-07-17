<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class SubmissionFileType extends Model
{
    use HasFactory, SortableTrait;

    protected $fillable = [
        'name',
    ];
}
