<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentManual extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, HasFactory;

    protected $guarded = ['id', 'scheduled_conference_id'];
}
