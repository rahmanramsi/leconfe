<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;

class TrackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('Track:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Track $track)
    {
        if ($user->can('Track:view')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('Track:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Track $track)
    {
        if ($user->can('Track:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Track $track)
    {
        if ($user->can('Track:delete')) {
            return true;
        }
    }
}
