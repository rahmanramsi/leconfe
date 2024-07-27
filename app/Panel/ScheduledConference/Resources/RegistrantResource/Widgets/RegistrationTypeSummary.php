<?php

namespace App\Panel\Series\Resources\RegistrantResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\RegistrationType;

class RegistrationTypeSummary extends Widget
{
    protected static string $view = 'panel.series.resources.registrant-resource.widgets.registration-type-summary';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $registrationTypes = RegistrationType::whereSerieId(app()->getCurrentSerieId())->get();

        return [
            'registrationTypes' => $registrationTypes,
        ];
    }
}
