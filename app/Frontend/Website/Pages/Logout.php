<?php

namespace App\Frontend\Website\Pages;

use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Logout extends Page
{
    function __invoke()
    {
		auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        if(app()->getCurrentScheduledConference()){
            return redirect()->route('livewirePageGroup.scheduledConference.pages.login');
        }

        if(app()->getCurrentConference()){
            return redirect()->route('livewirePageGroup.conference.pages.login');
        }

        return redirect()->route('livewirePageGroup.website.pages.login');
    }
}
