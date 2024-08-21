<?php

namespace App\Utils;

use App\Utils\UpgradeSchemas\UpgradeAlpha3;
use App\Utils\UpgradeSchemas\UpgradeBeta2;

class UpgradeSchema
{
    public static $schemas = [
        '1.0.0-alpha3' => UpgradeAlpha3::class,
        '1.0.0-beta2' => UpgradeBeta2::class
    ];

    public static function getSchemasByVersion(string $installedVersion, string $applicationVersion)
    {
        $filteredActions = [];

        foreach (static::$schemas as $upgradeVersion => $upgradeClass) {
            // filter upgrade script by comparing to database version and application version
            if (version_compare($installedVersion, $upgradeVersion, '<') && version_compare($applicationVersion, $upgradeVersion, '>=')) {
                $filteredActions[$upgradeVersion] = new $upgradeClass($installedVersion, $applicationVersion);
            }
        }

        return $filteredActions;
    }
}
