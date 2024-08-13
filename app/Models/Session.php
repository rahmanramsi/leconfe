<?php

namespace App\Models;

use App\Facades\Setting;
use Carbon\Carbon;
use App\Models\Timeline;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\Casts\Attribute;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, HasFactory;

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
            get: fn() => Str::squish(
                $this->getStartDate()->format(Setting::get('format_time')) .
                ' - ' .
                $this->getEndDate()->format(Setting::get('format_time'))
            ),
        );
    }

    public function getStartDate()
    {
        return $this->start_at->setTimezone(app()->getCurrentScheduledConference()->getMeta('timezone'));
    }

    public function getEndDate()
    {
        return $this->end_at->setTimezone(app()->getCurrentScheduledConference()->getMeta('timezone'));
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
        return !$this->isFuture() && !$this->isPast();
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
        if (!$this->isRequireAttendance()) {
            return false;
        }

        if (!$this->isOngoing()) {
            return false;
        }

        return true;
    }

    public function getRequiresAttendanceStatus(): string
    {
        if ($this->timeline->isRequireAttendance()) {
            return self::ATTENDANCE_STATUS_TIMELINE;
        }

        if (!$this->require_attendance) {
            return self::ATTENDANCE_STATUS_NOT_REQUIRED;
        }

        return self::ATTENDANCE_STATUS_REQUIRED;
    }

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }
}
