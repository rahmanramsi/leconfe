<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Frontend\Conference\Pages\Proceedings as PagesProceedings;
use App\Models\Enums\SubmissionStatus;
use App\Models\Proceeding;
use App\Models\Track;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;
use Illuminate\Support\Str;

class EditorialTeam extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.editorial-team';


    public function mount()
    {
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => __('general.home'),
            __('general.editorial_team')
        ];
    }


    public function getViewData(): array
    {
        return [
            'editorialTeam' => app()->getCurrentScheduledConference()?->getMeta('editorial_team'),
        ];
    }
}
