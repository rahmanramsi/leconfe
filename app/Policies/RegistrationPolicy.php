<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('Registration:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can enroll models.
     */
    public function enroll(User $user)
    {
        if ($user->can('Registration:enroll')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registration)
    {
        if ($user->can('Registration:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registration)
    {
        if ($user->can('Registration:delete')) {
            return true;
        }
    }
}
