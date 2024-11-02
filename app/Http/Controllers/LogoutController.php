<?php

namespace App\Http\Controllers;

class LogoutController extends Controller
{
    public function __invoke()
    {
        auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        if (app()->getCurrentScheduledConference()) {
            return redirect()->route('livewirePageGroup.scheduledConference.pages.login');
        }

        if (app()->getCurrentConference()) {
            return redirect()->route('livewirePageGroup.conference.pages.login');
        }

        return redirect()->route('livewirePageGroup.website.pages.login');
    }
}
