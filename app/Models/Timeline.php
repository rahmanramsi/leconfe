<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Agenda;
use App\Facades\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\Casts\Attribute;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timeline extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, HasFactory;

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
        'requires_attendance',
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

    protected function timeSpan(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::squish(
                $this->getEarliestTime()->format(Setting::get('format_time')) . 
                ' - ' . 
                $this->getLatestTime()->format(Setting::get('format_time'))
            ),
        );
    }

    public function getEarliestTime(): ?Carbon
    {
        $earliest_agenda = $this->agendas()
            ->orderBy('time_start', 'ASC')
            ->limit(1)
            ->first();

        if(!$earliest_agenda) {
            return now()->subDays(60);
        }

        return $earliest_agenda->date_start;
    }

    public function getLatestTime(): ?Carbon
    {
        $latest_agenda = $this->agendas()
            ->orderBy('time_end', 'DESC')
            ->limit(1)
            ->first();

        if(!$latest_agenda) {
            return now()->subDays(30);
        }

        return $latest_agenda->date_end;
    }

    public function isOngoing(): bool
    {
        return !$this->getEarliestTime()->isFuture() && !$this->getLatestTime()->isPast();
    }

    public function isRequiresAttendance(): bool
    {
        return $this->requires_attendance;
    }

    public function canShown(): bool
    {
        if(!$this->isRequiresAttendance()) {
            return false;
        }

        if($this->hide) {
            return false;
        }

        return true;
    }

    public function canAttend(): bool
    {
        if(!$this->canShown()) {
            return false;
        }

        if(!$this->isOngoing()) {
            return false;
        }

        return true;
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }
}
