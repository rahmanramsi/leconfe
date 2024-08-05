<?php

namespace App\Frontend\Website\Pages;

use Rahmanramsi\LivewirePageGroup\Pages\Page;

class AboutSystem extends Page
{
    protected static string $view = 'frontend.website.pages.about-system';


    public function mount()
    {
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'About System'
        ];
    }


    public function getViewData(): array
    {
        return [
            'version' => app()->getInstalledVersion(),
        ];
    }
}
