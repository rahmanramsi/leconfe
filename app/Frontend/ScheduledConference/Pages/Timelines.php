<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Timeline;
use Livewire\Attributes\Title;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Timelines extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.timelines';

    public function mount()
    {
        //
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Timelines',
        ];
    }

    protected function getViewData(): array
    {
        return [
            'timelines' => Timeline::query()
                ->where('hide', false)
                ->orderBy('date')    
                ->get(),
        ];
    }
}
