<?php

namespace App\Models;

use Carbon\Carbon;
use Plank\Metable\Metable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationType extends Model
{
    use 
        BelongsToScheduledConference, 
        HasShortflakePrimary,
        Cachable, 
        Metable,
        HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    public function getRegisteredUserCount()
    {
        return $this->registration()->count();
    }

    public function getPaidParticipantCount()
    {
        return $this->registration()->where('paid_at', '!=', null)->count();
    }

    public function getQuotaLeft()
    {
        return ($this->quota - $this->getPaidParticipantCount());
    }

    public function isExpired()
    {
        return Carbon::parse($this->closed_at)->diffInDays(now(), false) > 0;
    }

    public function getCost()
    {
        return $this->cost === 0 ? 'Free' : money($this->cost, $this->currency);
    }
    public function getCostWithCurrency()
    {
        return $this->cost === 0 ? 'Free' : ($this->currency === 'free' ? '' : ' (' . currency($this->currency)->getCurrency() . ') ' . money($this->cost, $this->currency));
    }

    public function registration(): HasMany
    {
        return $this->hasMany(Registration::class, 'registration_type_id', 'id');
    }
}
