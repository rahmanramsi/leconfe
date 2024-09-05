<?php

namespace App\Utils\UpgradeSchemas;

use App\Actions\MailTemplates\MailTemplatePopulateDefaultData;
use App\Models\Conference;
use App\Models\ScheduledConference;
use App\Models\Submission;
use App\Models\RegistrationType;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpgradeBeta5 extends UpgradeBase
{
	public function run(): void
	{
		Role::query()
			->withoutGlobalScopes()
			->lazy()
			->each(fn($role) => $role->permissions()->detach());
	}
}	