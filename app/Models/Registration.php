<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Enums\RegistrationPaymentState;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, HasFactory, SoftDeletes;

    protected $guarded = ['id', 'scheduled_conference_id'];

    // payment state [paid, unpaid] from registration_payments table
    public function getState(): string
    {
        return $this->registrationPayment->state;
    }

    public function getAttendance(null | Timeline | Session $data): ?RegistrationAttendance
    {
        if(!$data) {
            return null;
        }
        
        $attendance = RegistrationAttendance::where('registration_id', $this->id);

        if($data instanceof Timeline) {
            $attendance = $attendance->where('timeline_id', $data->id);
        }

        if($data instanceof Session) {
            $attendance = $attendance->where('session_id', $data->id);
        }

        return $attendance->first();
    }

    public function isAttended(null | Timeline | Session $data): bool
    {
        if(!$this->getAttendance($data)) {
            return false;
        }

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrationType(): BelongsTo
    {
        return $this->belongsTo(RegistrationType::class);
    }

    public function registrationPayment(): HasOne
    {
        return $this->hasOne(RegistrationPayment::class);
    }

    public function registrationAttendance(): HasMany
    {
        return $this->hasMany(RegistrationAttendance::class);
    }
}
