<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Pages;

use App\Models\RegistrationType;
use Filament\Resources\Pages\Page;
use App\Panel\ScheduledConference\Resources\RegistrantResource;

class ListTypeSummary extends Page
{
    protected static string $resource = RegistrantResource::class;

    protected static ?string $breadcrumb = 'Registration Type Stats';

    protected static string $view = 'panel.scheduledConference.resources.registrant-resource.pages.list-type-summary';

    public function getSubNavigation(): array
    {
        return static::$resource::getSubNavigation();
    }

    protected function getViewData(): array
    {
        $registrationTypes = RegistrationType::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->get();

        return [
            'registrationTypes' => $registrationTypes,
        ];
    }
}
