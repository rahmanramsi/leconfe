<?php

namespace App\Utils\UpgradeSchemas;

use App\Actions\Authors\AuthorRolePopulateDefaultDataAction;
use App\Models\Conference;

class UpgradeBeta3 extends UpgradeBase
{
    public function run(): void
    {
        Conference::lazy()->each(function ($conference) {
            AuthorRolePopulateDefaultDataAction::run($conference);
        });
    }
}
