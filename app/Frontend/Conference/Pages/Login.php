<?php

namespace App\Frontend\Conference\Pages;

use App\Frontend\Website\Pages\Login as WebsiteLogin;
class Login extends WebsiteLogin
{
    public function getRedirectUrl(): string
    {
        return route('filament.conference.pages.dashboard');
    }
}
