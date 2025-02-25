<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('Permission:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission)
    {
        if ($user->can('Permission:view')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('Permission:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission)
    {
        if ($user->can('Permission:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission)
    {
        if ($user->can('Permission:delete')) {
            return true;
        }
    }

    /**
     * Determine whether the user can assign the permissions.
     */
    public function assign(User $user, Permission $permission)
    {
        $protectedPermissionContexts = Permission::getProtectedPermissionContexts();
        if (in_array($permission->context, $protectedPermissionContexts) && ! $user->hasRole('Admin')) {
            return false;
        }

        if ($user->can('Permission:assign')) {
            return true;
        }
    }
}
