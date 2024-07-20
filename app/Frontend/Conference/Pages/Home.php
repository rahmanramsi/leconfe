<?php

namespace App\Frontend\Conference\Pages;

use App\Actions\Conferences\ConferenceUpdateAction;
use App\Models\Topic;
use App\Models\Venue;
use App\Models\Conference;
use App\Models\Submission;
use App\Models\SpeakerRole;
use Illuminate\Support\Str;
use App\Models\Announcement;
use App\Models\CommitteeRole;
use App\Models\Enums\ScheduledConferenceState;
use App\Models\ScheduledConference;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Home extends Page
{
    protected static string $view = 'frontend.conference.pages.home';

    public function mount()
    {
    }

    protected function getViewData(): array
    {
        $conference = app()->getCurrentConference();
        $pastScheduledConferences = ScheduledConference::query()
            ->with(['media', 'meta', 'conference'])
            ->where('state', ScheduledConferenceState::Archived)
            ->orderBy('date_start', 'desc')
            ->get();

        $nextScheduledConference = ScheduledConference::query()
            ->with(['media', 'meta', 'conference'])
            ->where('state', ScheduledConferenceState::Current)
            ->first();

        return [
            'conference' => $conference,
            'pastScheduledConferences' => $pastScheduledConferences,
            'nextScheduledConference' => $nextScheduledConference,
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
