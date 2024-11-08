<?php

namespace App\Frontend\Conference\Pages;

use App\Frontend\Website\Pages\Login as WebsiteLogin;

class Login extends WebsiteLogin
{
    public function getViewData(): array
    {
        return [
            'resetPasswordUrl' => route('livewirePageGroup.conference.pages.reset-password'),
            'registerUrl' => null,
        ];
    }

    public function getRedirectUrl(): string
    {
        return route('filament.conference.pages.dashboard');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.login'),
        ];
    }
}
