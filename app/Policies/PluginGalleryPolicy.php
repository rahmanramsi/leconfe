<?php

namespace App\Policies;

use App\Models\User;

class PluginGalleryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('PluginGallery:viewAny')) {
            return true;
        }
    }

    public function install(User $user)
    {
        if ($user->can('PluginGallery:install')) {
            return true;
        }
    }
}
