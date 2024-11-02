<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'registration_type_id',
    ];

    public function getState(): string
    {
        return $this->registrationPayment->state;
    }

    public function getAttendance(Timeline|Session $data): ?RegistrationAttendance
    {
        $attendance = RegistrationAttendance::where('registration_id', $this->id);

        if ($data instanceof Timeline) {
            $attendance = $attendance->where('timeline_id', $data->id);
        }

        if ($data instanceof Session) {
            $attendance = $attendance->where('session_id', $data->id);
        }

        return $attendance->first();
    }

    public function isAttended(null|Timeline|Session $data): bool
    {
        if (! $this->getAttendance($data)) {
            return false;
        }

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
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
