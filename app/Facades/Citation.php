<?php

namespace App\Facades;

use App\Managers\CitationManager;
use Illuminate\Support\Facades\Facade;

class Citation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CitationManager::class;
    }
}
