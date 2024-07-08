<?php

namespace App\Models\Concerns;

use App\Models\ScheduledConference;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToScheduledConference
{
    public static function bootBelongsToScheduledConference()
    {
        static::creating(function (Model $model) {
            if(App::getCurrentScheduledConferenceId()){
                $model->scheduled_conference_id ??= App::getCurrentScheduledConferenceId();
            }
        });
    }

    public function scheduledConference(): BelongsTo
    {
        return $this->belongsTo(ScheduledConference::class);
    }
}
