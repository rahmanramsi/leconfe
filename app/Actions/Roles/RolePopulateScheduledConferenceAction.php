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

class RolePopulateScheduledConferenceAction
{
    use AsAction;

    public function handle(ScheduledConference $scheduledConference)
    {
        foreach (UserRole::scheduledConferenceRoles() as $role) {
            $role = Role::withoutGlobalScopes()->firstOrCreate([
                'name' => $role->value,
                'conference_id' => $scheduledConference->conference_id,
                'scheduled_conference_id' => $scheduledConference->getKey(),
            ]);

        }
    }
}
