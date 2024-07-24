<?php

namespace App\Models\NavigationItemType;

use App\Models\NavigationMenuItem;

class Announcements extends BaseNavigationItemType
{
    public static function getId(): string
    {
        return 'announcements';
    }

    public static function getLabel(): string
    {
        return 'Announcements';
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        if(app()->getCurrentScheduledConferenceId()){
            return route('livewirePageGroup.scheduledConference.pages.announcement-list');
        }

        return parent::getUrl($navigationMenuItem);
    }
}
