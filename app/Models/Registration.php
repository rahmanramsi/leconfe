<?php

namespace App\Models;

use App\Models\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends Model
{
    use
        BelongsToScheduledConference,
        HasShortflakePrimary,
        Cachable,
        HasFactory;

    protected $guarded = ['id', 'serie_id'];

    public function getState()
    {
        return $this->state;
    }

    public function isTrashed()
    {
        return $this->trashed;
    }

    public function getStatus()
    {
        if ($this->isTrashed()) {
            return RegistrationStatus::Trashed->value;
        }
        return $this->getState();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration_type(): BelongsTo
    {
        return $this->belongsTo(RegistrationType::class);
    }
}
