<?php

namespace App\Policies;

use App\Models\RegistrationType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrationTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('RegistrationSetting:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('RegistrationSetting:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationType $registrationType)
    {
        if ($user->can('RegistrationSetting:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationType $registrationType)
    {
        if ($user->can('RegistrationSetting:delete')) {
            return true;
        }
    }
}
