<?php

namespace App\Panel\ScheduledConference\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [];
    }
}
