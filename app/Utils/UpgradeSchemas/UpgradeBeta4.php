<?php

namespace App\Utils\UpgradeSchemas;

use App\Actions\MailTemplates\MailTemplatePopulateDefaultData;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Enums\UserRole;
use Illuminate\Support\Facades\Artisan;
use App\Actions\Roles\RoleAssignDefaultPermissions;
use App\Actions\Permissions\PermissionPopulateAction;
use App\Models\Conference;
use App\Models\ScheduledConference;

class UpgradeBeta4 extends UpgradeBase
{
	public function run(): void
	{
		PermissionPopulateAction::run();

		Role::lazy()->each(function (Role $role) {
			RoleAssignDefaultPermissions::run($role);
		});

		Conference::lazy()->each(function (Conference $conference) {
			MailTemplatePopulateDefaultData::run($conference);
		});

		ScheduledConference::lazy()->each(function (ScheduledConference $scheduledConference) {
			$scheduledConference->setManyMeta([
				'timezone' => 'UTC',
				'submission_payment' => true,
			]);
		});

		Artisan::call('migrate');
	}
}	