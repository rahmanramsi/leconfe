<?php

namespace App\Actions\Roles;

use App\Models\Conference;
use App\Models\Enums\UserRole;
use App\Models\Role;
use Lorisleiva\Actions\Concerns\AsAction;

class RolePopulateConferenceAction
{
    use AsAction;

    public function handle(Conference $conference)
    {
        foreach (UserRole::conferenceRoles() as $role) {
            $role = Role::firstOrCreate(['name' => $role->value, 'conference_id' => $conference->getKey()]);
        }
    }
}
