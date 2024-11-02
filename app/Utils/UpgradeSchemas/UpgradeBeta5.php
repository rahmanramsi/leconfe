<?php

namespace App\Utils\UpgradeSchemas;

use App\Actions\Roles\RolePopulateScheduledConferenceAction;
use App\Models\Role;
use App\Models\ScheduledConference;

class UpgradeBeta5 extends UpgradeBase
{
    public function run(): void
    {

        Role::query()
            ->withoutGlobalScopes()
            ->lazy()
            ->each(fn ($role) => $role->permissions()->detach());

        Role::query()
            ->withoutGlobalScopes()
            ->whereIn('name', ['Series Manager'])
            ->lazy()
            ->each(fn ($role) => $role->delete());

        Role::query()
            ->withoutGlobalScopes()
            ->where('name', 'Conference Editor')
            ->update(['name' => 'Scheduled Conference Editor']);

        ScheduledConference::withoutGlobalScopes()
            ->lazy()
            ->each(fn ($scheduledConference) => RolePopulateScheduledConferenceAction::run($scheduledConference));

    }
}
