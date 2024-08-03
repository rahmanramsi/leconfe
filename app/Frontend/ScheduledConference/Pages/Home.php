<?php

namespace App\Frontend\ScheduledConference\Pages;

use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Home extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.home';

    public function mount()
    {
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();
        $currentScheduledConference->load([
            'speakerRoles.speakers' => ['meta'],
        ]);

        return [
            'currentScheduledConference' => $currentScheduledConference,
        ];
    }


    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get('/', static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
