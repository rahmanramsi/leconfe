<?php

namespace App\Panel\Conference\Pages;

use App\Models\Enums\UserRole;
use App\Panel\Conference\Resources\ScheduledConferenceResource\Pages\ManageScheduledConferences;
use App\Panel\ScheduledConference\Resources\SubmissionResource;
use App\Panel\Conference\Widgets;
use Filament\Pages\Dashboard as PagesDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends PagesDashboard
{
    public function mount()
    {
        return redirect()->to(ManageScheduledConferences::getUrl());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function internalRoles(): array
    {
        return [
            UserRole::Admin->value,
            UserRole::ConferenceManager->value,
            UserRole::SeriesManager->value,
            UserRole::ConferenceEditor->value,
        ];
    }

}
