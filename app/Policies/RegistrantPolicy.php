<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->can('Registrant:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function enroll(User $user): bool
    {
        if($user->can('Registrant:enroll')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registrant): bool
    {
        if($user->can('Registrant:edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registrant): bool
    {
        if($user->can('Registrant:delete')) {
            return true;
        }
    }
}
