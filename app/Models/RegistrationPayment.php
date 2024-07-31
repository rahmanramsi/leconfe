<?php

namespace App\Models;

use Plank\Metable\Metable;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use App\Models\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationPayment extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, Metable, HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];

    public static function getTypes()
    {
        return [
            RegistrationStatus::Paid => 'Paid',
            RegistrationStatus::Unpaid => 'Unpaid',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
