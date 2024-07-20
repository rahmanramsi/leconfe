<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Login as WebsiteLogin;

class Login extends WebsiteLogin
{
    
    public function getRedirectUrl(): string
    {
        return route('filament.scheduledConference.pages.dashboard');
    }
}
