<?php

namespace App\Models\NavigationItemType;

use App\Facades\Setting;
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
        return app()->getCurrentScheduledConferenceId() && Setting::get('allow_registration') && !auth()->check();
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        if(app()->getCurrentScheduledConferenceId()){
            return route('livewirePageGroup.scheduledConference.pages.register');
        }

        if(app()->getCurrentConferenceId()){
            $currentScheduledConference = app()->getCurrentConference()->currentScheduledConference;

            return route('livewirePageGroup.scheduledConference.pages.register', ['serie' => $currentScheduledConference]);
        }

        return route('livewirePageGroup.website.pages.register');
    }
}
