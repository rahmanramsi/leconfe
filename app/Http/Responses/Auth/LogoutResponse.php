<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        if (app()->getCurrentScheduledConference()) {
            return redirect()->route('livewirePageGroup.scheduledConference.pages.login');
        }

        if (app()->getCurrentConference()) {
            return redirect()->route('livewirePageGroup.conference.pages.login');
        }

        return redirect()->route('livewirePageGroup.website.pages.login');
    }
}
