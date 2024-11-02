<?php

namespace App\Policies;

use App\Models\RegistrationAttendance;
use App\Models\User;

class RegistrationAttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('Attendance:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can mark in models.
     */
    public function markIn(User $user)
    {
        if ($user->can('Attendance:markIn')) {
            return true;
        }
    }

    /**
     * Determine whether the user can mark out in model.
     */
    public function markOut(User $user)
    {
        if ($user->can('Attendance:markOut')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationAttendance $registrationAttendance)
    {
        if ($user->can('Attendance:delete')) {
            return true;
        }
    }
}
