<?php

namespace App\Policies;

use App\Models\RegistrationAttendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrationAttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('Attendance:viewAny')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can mark in models.
     */
    public function markIn(User $user): bool
    {
        if ($user->can('Attendance:markIn')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can mark out in model.
     */
    public function markOut(User $user): bool
    {
        if ($user->can('Attendance:markOut')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationAttendance $registrationAttendance): bool
    {
        if ($user->can('Attendance:delete')) {
            return true;
        }

        return false;
    }
}
