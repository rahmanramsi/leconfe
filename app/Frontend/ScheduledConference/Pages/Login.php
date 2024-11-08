<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Login as WebsiteLogin;

class Login extends WebsiteLogin
{
    public function getViewData(): array
    {
        return [
            'resetPasswordUrl' => route('livewirePageGroup.scheduledConference.pages.reset-password'),
            'registerUrl' => route('livewirePageGroup.scheduledConference.pages.register'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.login'),
        ];
    }

    public function getRedirectUrl(): string
    {
        return route('filament.scheduledConference.pages.dashboard');
    }
}
