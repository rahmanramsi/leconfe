<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationAttendance extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $fillable = [
        'timeline_id',
        'session_id',
        'registration_id',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
