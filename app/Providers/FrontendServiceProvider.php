<?php

namespace App\Providers;

use App\Http\Middleware\IdentifyConference;
use App\Http\Middleware\SetupConference;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Rahmanramsi\LivewirePageGroup\Facades\LivewirePageGroup;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use App\Http\Responses\Auth\LogoutResponse;

class FrontendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->resolving('livewire-page-group', function () {
            LivewirePageGroup::registerPageGroup(
                $this->websitePageGroup(PageGroup::make()),
            );
            LivewirePageGroup::registerPageGroup(
                $this->conferencePageGroup(PageGroup::make()),
            );
            LivewirePageGroup::registerPageGroup(
                $this->scheduledConferencePageGroup(PageGroup::make()),
            );

            Livewire::addPersistentMiddleware([
                'web',
                IdentifyConference::class,
            ]);
        });

        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/frontend/website/components'), 'website');
        Blade::anonymousComponentPath(resource_path('views/frontend/scheduledConference/components'), 'scheduledConference'); 
    }

    public function websitePageGroup(PageGroup $pageGroup): PageGroup
    {
        return $pageGroup
            ->id('website')
            ->path('')
            ->layout('frontend.website.components.layouts.app')
            ->bootUsing(function () {
            })
            ->middleware([
                'web',
            ], true)
            ->discoverPages(in: app_path('Frontend/Website/Pages'), for: 'App\\Frontend\\Website\\Pages');
    }

    public function conferencePageGroup(PageGroup $pageGroup): PageGroup
    {
        return $pageGroup
            ->id('conference')
            ->path('{conference:path}')
            ->layout('frontend.website.components.layouts.app')
            ->middleware([
                'web',
                IdentifyConference::class,
            ], true)
            ->discoverPages(in: app_path('Frontend/Conference/Pages'), for: 'App\\Frontend\\Conference\\Pages');
    }

    public function scheduledConferencePageGroup(PageGroup $pageGroup): PageGroup
    {
        return $pageGroup
            ->id('scheduledConference')
            ->path('{conference:path}/scheduled/{serie:path}')
            ->layout('frontend.website.components.layouts.app')
            ->bootUsing(function () {

            })
            ->middleware([
                'web',
                IdentifyConference::class,
            ], true)
            ->discoverPages(in: app_path('Frontend/ScheduledConference/Pages'), for: 'App\\Frontend\\ScheduledConference\\Pages');
    }
}
