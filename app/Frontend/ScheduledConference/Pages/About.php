<?php

namespace App\Frontend\ScheduledConference\Pages;

use Livewire\Attributes\Title;
use Illuminate\Contracts\Support\Htmlable;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class About extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.about';

    public function mount()
    {
    }

    public function getTitle(): string|Htmlable
    {
        return 'About the Conference';
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'About',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'about' => app()->getCurrentScheduledConference()?->getMeta('about')
        ];
    }
}
