<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Agenda;
use App\Models\Timeline;
use Illuminate\Support\Facades\Route;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class ParticipantAttendance extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-attendance';

    protected static ?string $slug = 'attendance';

    protected $listeners = ['openModal'];

    public $isOpen = false;

    public function mount()
    {
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $timelines = Timeline::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->orderBy('date', 'ASC')
            ->get();

        return [
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'timelines' => $timelines,
        ];
    }

    public static function routes(PageGroup $pageGroup): void
    {
        $slug = static::getSlug();
        Route::get("/{$slug}", static::class)
            ->middleware(static::getRouteMiddleware($pageGroup))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($pageGroup))
            ->name((string) str($slug)->replace('/', '.'));
    }
}
