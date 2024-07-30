<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use App\Models\Concerns\BelongsToScheduledConference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'conference_id',
        'scheduled_conference_id',
        'guard_name',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('conferences', function (Builder $builder) {
            
            $conferenceScopeColumn =  config('permission.table_names.roles', 'roles') . '.conference_id';
            $scheduledConferenceScopeColumn =  config('permission.table_names.roles', 'roles') . '.scheduled_conference_id';
            
            $conferenceId = app()->getCurrentConferenceId();
            $builder->where($conferenceScopeColumn, 0);
            if($conferenceId){
                $builder->orWhere($conferenceScopeColumn, app()->getCurrentConferenceId());
            }
            
            $scheduledConferenceId = app()->getCurrentScheduledConferenceId();
            $builder->where($scheduledConferenceScopeColumn, 0);
            if($scheduledConferenceId){
                $builder->orWhere($scheduledConferenceScopeColumn, app()->getCurrentScheduledConferenceId());
            }
        });
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function scheduledConference(): BelongsTo
    {
        return $this->belongsTo(ScheduledConference::class);
    }
}
