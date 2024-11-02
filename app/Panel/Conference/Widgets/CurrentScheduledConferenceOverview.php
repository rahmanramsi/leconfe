<?php

namespace App\Panel\Conference\Widgets;

use Filament\Widgets\Widget;

class CurrentScheduledConferenceOverview extends Widget
{
    protected static bool $isLazy = false;

    protected static string $view = 'panel.conference.widgets.current-scheduled-conference-overview';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $currentConference = app()->getCurrentConference();
        $currentScheduledConference = $currentConference->currentScheduledConference;

        return [
            'currentConference' => $currentConference,
            'currentScheduledConference' => $currentScheduledConference,
        ];
    }
}
