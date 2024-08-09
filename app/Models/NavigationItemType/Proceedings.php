<?php

namespace App\Models\NavigationItemType;

use App\Models\NavigationMenuItem;
use App\Models\Proceeding;

class Proceedings extends BaseNavigationItemType
{
    public static function getId(): string
    {
        return 'proceedings';
    }

    public static function getLabel(): string
    {
        return 'Proceedings';
    }

    public static function getIsDisplayed(NavigationMenuItem $navigationMenuItem): bool
    {
        return app()->getCurrentConferenceId() && Proceeding::count() > 0;
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        return route('livewirePageGroup.conference.pages.proceedings');
    }
}
