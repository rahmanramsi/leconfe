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
    public function getState()
    {
        return $this->registrationPayment->state;
    }

    public function getAttendance()
    {
        return $this->attend;
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
