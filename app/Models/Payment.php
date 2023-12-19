<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use App\Models\Enums\PaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Metable\Metable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use Metable, InteractsWithMedia, BelongsToConference;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => PaymentState::Pending,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'state' => PaymentState::class,
        'paid_at' => 'datetime',
    ];

    public static function booted()
    {
        // static::saving(function (Model $model) {
        //     if($model->state === PaymentState::Paid){
        //         $model->paid_at = now();
        //     }
        // });
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
