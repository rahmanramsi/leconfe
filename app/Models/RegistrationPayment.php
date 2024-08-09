<?php

namespace App\Models;

use Plank\Metable\Metable;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use App\Models\Concerns\BelongsToScheduledConference;
use App\Models\Enums\RegistrationPaymentState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationPayment extends Model
{
    use BelongsToScheduledConference, HasShortflakePrimary, Cachable, Metable, HasFactory;

    protected $fillable = [
        'type',
        'name',
        'description',
        'cost',
        'currency',
        'state',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public static function getTypes()
    {
        return [
            RegistrationPaymentState::Paid => 'Paid',
            RegistrationPaymentState::Unpaid => 'Unpaid',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
