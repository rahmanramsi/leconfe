<?php

namespace App\Models;

use App\Models\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, HasFactory;

    public const TRASHED = 'Trashed';

    protected $guarded = ['id', 'scheduled_conference_id'];

    // payment state [paid, unpaid] from registration_payments table
    public function getState()
    {
        return $this->registrationPayment->state;
    }

    public function isTrashed()
    {
        return $this->trashed;
    }

    public function getStatus()
    {
        if($this->isTrashed()) {
            return RegistrationStatus::Trashed->value;
        }

        return $this->getState();
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
}
