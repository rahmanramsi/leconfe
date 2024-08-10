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
        'date',
        'time_start',
        'time_end',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time_start' => 'datetime',
        'time_end' => 'datetime',
    ];

    public const ATTENDANCE_STATUS_TIMELINE = 'timeline';
    public const ATTENDANCE_STATUS_REQUIRED = 'required';
    public const ATTENDANCE_STATUS_NOT_REQUIRED = 'not-required';

    protected function timeSpan(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::squish(
                $this->time_start->format(Setting::get('format_time')) .
                ' - ' .
                $this->time_end->format(Setting::get('format_time'))
            ),
        );
    }

    protected function dateStart(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->setTimeFromTimeString($this->time_start),
        );
    }

    protected function dateEnd(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->setTimeFromTimeString($this->time_end),
        );
    }

    public function isFuture(): bool
    {
        return $this->date_start->isFuture();
    }

    public function isPast(): bool
    {
        return $this->date_end->isPast();
    }

    public function isOngoing(): bool
    {
        return !$this->isFuture() && !$this->isPast();
    }

    public function isRequiresAttendance(): bool
    {
        if ($this->timeline()->first()->isRequiresAttendance()) {
            return false;
        }

        return $this->require_attendance;
    }

    public function canAttend(): bool
    {
        if (!$this->isRequiresAttendance()) {
            return false;
        }

        if (!$this->isOngoing()) {
            return false;
        }

        return true;
    }

    public function getRequiresAttendanceStatus(): string
    {
        if ($this->timeline()->first()->isRequiresAttendance()) {
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
