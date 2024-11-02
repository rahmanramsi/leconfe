<?php

namespace App\Models;

use App\Facades\Setting;
use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Session extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $fillable = [
        'timeline_id',
        'name',
        'public_details',
        'details',
        'require_attendance',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public const ATTENDANCE_STATUS_TIMELINE = 'timeline';

    public const ATTENDANCE_STATUS_REQUIRED = 'required';

    public const ATTENDANCE_STATUS_NOT_REQUIRED = 'not-required';

    protected function timeSpan(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::squish(
                $this->getStartDate()->format(Setting::get('format_time')).
                ' - '.
                $this->getEndDate()->format(Setting::get('format_time'))
            ),
        );
    }

    public function getStartDate()
    {
        $timezone = app()->getCurrentScheduledConference()->getMeta('timezone');

        return $this->start_at->setTimezone($timezone);
    }

    public function getEndDate()
    {
        $timezone = app()->getCurrentScheduledConference()->getMeta('timezone');

        return $this->end_at->setTimezone($timezone);
    }

    public function isFuture(): bool
    {
        return $this->getStartDate()->isFuture();
    }

    public function isPast(): bool
    {
        return $this->getEndDate()->isPast();
    }

    public function isOngoing(): bool
    {
        return ! $this->isFuture() && ! $this->isPast();
    }

    public function isRequireAttendance(): bool
    {
        if ($this->timeline->isRequireAttendance()) {
            return false;
        }

        return $this->require_attendance;
    }

    public function canAttend(): bool
    {
        if (! $this->isRequireAttendance()) {
            return false;
        }

        if (! $this->isOngoing()) {
            return false;
        }

        return true;
    }

    public function getRequiresAttendanceStatus(): string
    {
        if ($this->timeline->isRequireAttendance()) {
            return self::ATTENDANCE_STATUS_TIMELINE;
        }

        if (! $this->require_attendance) {
            return self::ATTENDANCE_STATUS_NOT_REQUIRED;
        }

        return self::ATTENDANCE_STATUS_REQUIRED;
    }

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }
}
