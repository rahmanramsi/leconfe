<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Website\Pages\Page;

class PrivacyStatement extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.privacy-statement';

    public function mount()
    {
        //
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.privacy_statement'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'privacyStatement' => app()->getCurrentScheduledConference()->getMeta('privacy_statement'),
        ];
    }
}
