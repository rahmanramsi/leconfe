<?php

namespace App\Utils\UpgradeSchemas;

use App\Actions\MailTemplates\MailTemplatePopulateDefaultData;
use App\Models\Role;
use App\Actions\Roles\RoleAssignDefaultPermissions;
use App\Actions\Permissions\PermissionPopulateAction;
use App\Models\Conference;
use App\Models\ScheduledConference;
use App\Models\Submission;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpgradeBeta4 extends UpgradeBase
{
	public function run(): void
	{
		$this->addColumns();

		Conference::lazy()->each(function (Conference $conference) {
			MailTemplatePopulateDefaultData::run($conference);
		});

		PermissionPopulateAction::run();

		Role::lazy()->each(function (Role $role) {
			RoleAssignDefaultPermissions::run($role);
		});
	}

	public function addColumns(): void
	{
		if(!Schema::hasColumn('registrations', (new Submission())->getForeignKey())) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreignIdFor(Submission::class)->nullable()->constrained()->cascadeOnDelete();
            });
        }
        
        if(!Schema::hasColumn('registration_payments', 'level')) {
            Schema::table('registration_payments', function (Blueprint $table) {
                $table->unsignedInteger('level')->default(RegistrationType::LEVEL_PARTICIPANT);
            });
        }
	}
}	