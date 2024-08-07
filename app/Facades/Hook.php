<?php

namespace App\Facades;

use App\Managers\HookManager;
use Illuminate\Support\Facades\Facade;

class Hook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HookManager::class;
    }
}
