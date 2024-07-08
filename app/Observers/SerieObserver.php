<?php

namespace App\Observers;

use App\Actions\Committees\CommitteeRolePopulateDefaultDataAction;
use App\Actions\Speakers\SpeakerRolePopulateDefaultDataAction;
use App\Models\ScheduledConference;

class SerieObserver
{
    /**
     * Handle the Serie "created" event.
     */
    public $afterCommit = true;
    
    public function created(ScheduledConference $scheduledConference): void
    {
        CommitteeRolePopulateDefaultDataAction::run($scheduledConference);
        SpeakerRolePopulateDefaultDataAction::run($scheduledConference);
    }

    /**
     * Handle the Serie "updated" event.
     */
    public function updated(ScheduledConference $scheduledConference): void
    {
        //
    }

    /**
     * Handle the Serie "deleted" event.
     */
    public function deleted(ScheduledConference $scheduledConference): void
    {
        //
    }

    /**
     * Handle the Serie "restored" event.
     */
    public function restored(ScheduledConference $scheduledConference): void
    {
        //
    }

    /**
     * Handle the Serie "force deleted" event.
     */
    public function forceDeleted(ScheduledConference $scheduledConference): void
    {
        //
    }
}
