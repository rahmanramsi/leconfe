<?php

namespace App\Conference\Pages;

use Rahmanramsi\LivewirePageGroup\Pages\Page;

class PrivacyStatement extends Page
{
    protected static string $view = 'conference.pages.privacy-statement';

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
