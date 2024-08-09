<?php

namespace App\Models\NavigationItemType;

use App\Facades\Setting;
use App\Models\NavigationMenuItem;
use App\Models\Timeline;

class Agenda extends BaseNavigationItemType
{
    public static function getId(): string
    {
        return 'agenda';
    }

    public static function getLabel(): string
    {
        return 'Agenda';
    }

    public static function getIsDisplayed(NavigationMenuItem $navigationMenuItem): bool
    {
        return app()->getCurrentScheduledConferenceId();
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        return route('livewirePageGroup.scheduledConference.pages.agenda');
    }
}
