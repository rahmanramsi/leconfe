<?php

namespace App\Models\NavigationItemType;

use App\Models\NavigationMenuItem;
use App\Models\Timeline;

class ParticipantRegistration extends BaseNavigationItemType
{
    public static function getId(): string
    {
        return 'participant-registration';
    }

    public static function getLabel(): string
    {
        return 'Participant Registration';
    }

    public static function getIsDisplayed(NavigationMenuItem $navigationMenuItem): bool
    {
        return app()->getCurrentScheduledConferenceId() && Timeline::isRegistrationOpen();
    }

    public static function getUrl(NavigationMenuItem $navigationMenuItem): string
    {
        return route('livewirePageGroup.scheduledConference.pages.participant-registration');
    }
}
