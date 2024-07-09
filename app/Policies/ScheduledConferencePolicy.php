<?php

namespace App\Policies;

use App\Models\ScheduledConference;
use App\Models\User;

class ScheduledConferencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('ScheduledConference:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduledConference $scheduledConference)
    {
        if ($user->can('ScheduledConference:view')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('ScheduledConference:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduledConference $scheduledConference)
    {
        if ($user->can('ScheduledConference:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduledConference $scheduledConference)
    {
        if ($user->can('ScheduledConference:delete')) {
            return true;
        }
    }
}
