<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('Session:viewAny')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('Session:create')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Session $session): bool
    {
        if ($user->can('Session:update')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Session $session): bool
    {
        if ($user->can('Session:delete')) {
            return true;
        }

        return false;
    }
}
