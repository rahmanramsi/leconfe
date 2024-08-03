<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timeline extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    public const TYPE_SUBMISSION_OPEN = 1;
    public const TYPE_SUBMISSION_CLOSE = 2;
    public const TYPE_REGISTRATION_OPEN = 3;
    public const TYPE_REGISTRATION_CLOSE = 4;

    protected $fillable = [
        'scheduled_conference_id',
        'name',
        'description',
        'date',
        'type',
        'hide',
    ];

    protected $casts = [
        'roles' => 'array',
        'date' => 'datetime',
        'hide' => 'boolean',
    ];

    public static function getTypes(): array
    {
        return [
            self::TYPE_SUBMISSION_OPEN => "Submission Open",
            self::TYPE_SUBMISSION_CLOSE => "Submission Close",
            self::TYPE_REGISTRATION_OPEN => "Registration Open",
            self::TYPE_REGISTRATION_CLOSE => "Registration Close",
        ];
    }

    public static function isSubmissionOpen(): bool
    {
        $timelineSubmissionOpen = self::where('type', self::TYPE_SUBMISSION_OPEN)->first();
        $timelineSubmissionClose = self::where('type', self::TYPE_SUBMISSION_CLOSE)->first();

        if (!$timelineSubmissionOpen) {
            return false;
        }

        if ($timelineSubmissionOpen->date->isPast() && (!$timelineSubmissionClose || $timelineSubmissionClose->date->isFuture())) {
            return true;
        }

        return false;
    }

    public static function isRegistrationOpen(): bool
    {
        $timelineRegistrationOpen = self::where('type', self::TYPE_REGISTRATION_OPEN)->first();
        $timelineRegistrationClose = self::where('type', self::TYPE_REGISTRATION_CLOSE)->first();

        if (!$timelineRegistrationOpen) {
            return false;
        }

        if ($timelineRegistrationOpen->date->isPast() && (!$timelineRegistrationClose || $timelineRegistrationClose->date->isFuture())) {
            return true;
        }

        return false;
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }
}
