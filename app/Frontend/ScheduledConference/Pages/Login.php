<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Login as WebsiteLogin;

class Login extends WebsiteLogin
{
    public ?string $redirect = null;

    public function mount()
    {
        parent::mount();

        $this->redirect = request()->query('redirect');
    }

    public function getViewData() : array 
    {
        return [
            'registerUrl' => route('livewirePageGroup.scheduledConference.pages.register'),
        ];
    }

    public function getRedirectUrl(): string
    {
        if($this->redirect === 'attendance') {
            return route('livewirePageGroup.scheduledConference.pages.attendance');
        }

        return route('filament.scheduledConference.pages.dashboard');
    }
}
