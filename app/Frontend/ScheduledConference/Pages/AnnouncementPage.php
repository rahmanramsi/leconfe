<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Announcement;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use App\Frontend\Website\Pages\Page;

class AnnouncementPage extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.announcement';

    public Announcement $announcement;

    public function getTitle(): string|Htmlable
    {
        return $this->announcement->title;
    }


    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            route(Announcements::getRouteName()) => __('general.announcement'),
            $this->announcement->title,
        ];
    }

    protected function getViewData(): array
    {
        return [];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();

        Route::get('announcements/view/{announcement}', static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
