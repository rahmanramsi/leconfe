<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Agenda;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\Timeline;
use App\Models\Registration;
use Illuminate\Support\Facades\Route;
use App\Models\RegistrationAttendance;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class ParticipantAttendance extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-attendance';

    protected static ?string $slug = 'attendance';

    protected $listeners = ['attend'];

    public ?Timeline $timelineData = null;

    public bool $isOpen = false;

    public ?string $errorMessage = null;

    public function mount()
    {
    }

    public function attend($timeline_id): void
    {
        $timeline = Timeline::where('id', $timeline_id)->first();
        if(!$timeline) return;

        $this->timelineData = $timeline;
        $this->isOpen = true;
    }

    public function cancel(): void
    {
        $this->timelineData = null;
        $this->isOpen = false;
    }

    public function confirm(): void
    {
        if(!auth()->check()) {
            // belum login
            $this->errorMessage = "You're not logged in";
            return;
        }

        $timeline = $this->timelineData;
        $registration = Registration::withTrashed()
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->whereUserId(auth()->user()->id)
            ->first();

        if(!$timeline) {
            // timeline yang dipilih tidak valid
            $this->errorMessage = "Invalid event selection";
            return;
        }
        if(!$registration) {
            // tidak melakukan registrasi
            $this->errorMessage = "You're not registrant of " . app()->getCurrentScheduledConference()->title;
            return;
        }
        if($registration->registrationPayment->state !== RegistrationPaymentState::Paid->value) { 
            // belum melakukan pembayaran
            $this->errorMessage = "You're not participant of " . app()->getCurrentScheduledConference()->title;
            return;
        }

        RegistrationAttendance::create([
            'timeline_id' => $timeline->id,
            'registration_id' => $registration->id,
        ]);

        $this->cancel();
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $timelines = Timeline::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->orderBy('date', 'ASC')
            ->get();

        $timelineData = null;
        if(!empty($this->timelineData)) {
            $timelineData = $this->timelineData;
        }

        return [
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'timelines' => $timelines,
            // data confirmation
            'timelineData' => $timelineData,
            'errorMessage' => $this->errorMessage,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Participant Attendance',
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
