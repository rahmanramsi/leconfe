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
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('Attendance:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationAttendance $registrationAttendance): bool
    {
        if ($user->can('Attendance:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationAttendance $registrationAttendance): bool
    {
        if ($user->can('Attendance:delete')) {
            return true;
        }
    }
}
