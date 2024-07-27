<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
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

    public function getStatus()
    {
        if(!$this->is_trashed) {
            if($this->paid_at === null)
                return 'unpaid';
            return 'paid';
        }
        return 'trash';
    }

    public function getPublicStatus()
    {
        switch ($this->getStatus()) {
            case 'trash':
                return 'failed';
            default:
                return $this->getStatus();
        }
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
