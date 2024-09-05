<?php

namespace App\Actions\Roles;

use App\Models\Conference;
use App\Models\Enums\UserRole;
use App\Models\Role;
use App\Models\ScheduledConference;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Yaml\Yaml;

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
