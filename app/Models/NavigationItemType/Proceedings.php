<?php

namespace App\Models\NavigationItemType;

use App\Models\NavigationMenuItem;

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

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        if(app()->getCurrentConferenceId()){
            return route('livewirePageGroup.conference.pages.proceedings');
        }

        return '#';
    }
}
