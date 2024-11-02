<?php

namespace App\Models;

use App\Models\Concerns\BelongsToScheduledConference;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentManual extends Model
{
    use BelongsToScheduledConference, Cachable, HasFactory;

    protected $fillable = [
        'name',
        'currency',
        'detail',
        'order_column',
    ];
}
