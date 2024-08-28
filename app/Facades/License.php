<?php

namespace App\Facades;

use App\Managers\CitationManager;
use App\Managers\HookManager;
use App\Managers\LicenseManager;
use Illuminate\Support\Facades\Facade;

class License extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LicenseManager::class;
    }
}
