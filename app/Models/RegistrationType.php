<?php

namespace App\Models;

use Carbon\Carbon;
use Plank\Metable\Metable;
use App\Models\Concerns\BelongsToScheduledConference;
use App\Models\Enums\RegistrationPaymentState;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationType extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, Metable, HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    public function getRegisteredUserCount()
    {
        return $this->registration()->count();
    }

    public function getPaidParticipantCount()
    {
        return $this->registration()
            ->whereHas('registrationPayment', function ($query) {
                $query->where('state', RegistrationPaymentState::Paid->value);
            })
            ->count();
    }

    public function getQuotaLeft()
    {
        return ($this->quota - $this->getPaidParticipantCount());
    }

    public function isQuotaFull()
    {
        return $this->getQuotaLeft() <= 0;
    }

    public function isExpired()
    {
        return Carbon::parse($this->closed_at)->diffInDays(now(), false) > 0;
    }

    public function isInvalid()
    {
        return $this->isQuotaFull() || $this->isExpired();
    }

    public function registration(): HasMany
    {
        return $this->hasMany(Registration::class, 'registration_type_id', 'id');
    }
}
