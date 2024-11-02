<?php

namespace App\Panel\ScheduledConference\Pages;

use App\Panel\ScheduledConference\Resources\SubmissionResource\Pages\ManageSubmissions;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount()
    {
        if (! auth()->user()->can('view', app()->getCurrentScheduledConference())) {
            return redirect()->to(ManageSubmissions::getUrl());
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view', app()->getCurrentScheduledConference());
    }
}
