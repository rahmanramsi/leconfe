<?php

namespace App\Panel\ScheduledConference\Resources\RegistrantResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\RegistrationType;

class RegistrationTypeSummary extends Widget
{
    protected static string $view = 'panel.scheduledConference.resources.registrant-resource.widgets.registration-type-summary';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $registrationTypes = RegistrationType::whereScheduledConferenceId(app()->getCurrentScheduledConferenceId())->get();

        return [
            'registrationTypes' => $registrationTypes,
        ];
    }
}
