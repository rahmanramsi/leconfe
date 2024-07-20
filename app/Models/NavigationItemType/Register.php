<?php

namespace App\Models\NavigationItemType;

use App\Models\NavigationMenuItem;

class Register extends BaseNavigationItemType
{
    public static function getId(): string
    {
        return 'register';
    }

    public static function getLabel(): string
    {
        return 'Register';
    }

    public static function getIsDisplayed(NavigationMenuItem $navigationMenuItem): bool
    {
        return ! auth()->check();
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        if(app()->getCurrentScheduledConferenceId()){
            return route('livewirePageGroup.scheduledConference.pages.register');
        }

        if(app()->getCurrentConferenceId()){
            return route('livewirePageGroup.conference.pages.register');
        }

        return route('livewirePageGroup.website.pages.register');
    }
}
