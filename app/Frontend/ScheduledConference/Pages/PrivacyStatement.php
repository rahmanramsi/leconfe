<?php

namespace App\Frontend\ScheduledConference\Pages;

use Livewire\Attributes\Title;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class PrivacyStatement extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.privacy-statement';

    public function mount()
    {
        //
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [

        ];
    }
}
