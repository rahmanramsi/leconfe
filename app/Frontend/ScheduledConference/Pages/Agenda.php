<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Session;
use App\Models\Enums\RegistrationPaymentState;
use App\Models\Timeline;
use App\Models\Registration;
use Illuminate\Support\Facades\Route;
use App\Models\RegistrationAttendance;
use Illuminate\Support\Arr;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class Agenda extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.agenda';

    protected static ?string $slug = 'agenda';

    public ?string $typeData = null;

    public ?Timeline $timelineData = null;

    public ?Session $sessionData = null;

    public bool $isOpen = false;

    public ?string $errorMessage = null;

    public const ATTEND_TYPE_TIMELINE = 'timeline';
    public const ATTEND_TYPE_SESSION = 'session';

    public function mount()
    {
    }

    public function attend($id, $type): void
    {
        if($type === self::ATTEND_TYPE_TIMELINE) {
            $timeline = Timeline::where('id', $id)->first();

            if(!$timeline) return;

            $this->timelineData = $timeline;
        } else {
            $session = Session::where('id', $id)->first();

            if(!$session) return;

            $this->sessionData = $session;
        }
        $this->typeData = $type;
        $this->isOpen = true;
    }

    public function cancel(): void
    {
        $this->typeData = null;
        $this->timelineData = null;
        $this->sessionData = null;
        $this->errorMessage = false;
        $this->isOpen = false;
    }

    public function confirm(): void
    {
        if(!auth()->check()) {
            $this->errorMessage = "You're not logged in";
            return;
        }

        $typeData = $this->typeData;

        $registration = Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();

        if(!$registration) {
            $this->errorMessage = "You're not registrant of " . app()->getCurrentScheduledConference()->title;
            return;
        }

        if($registration->registrationPayment->state !== RegistrationPaymentState::Paid->value) { 
            $this->errorMessage = "You're not participant of " . app()->getCurrentScheduledConference()->title;
            return;
        }

        if($typeData === self::ATTEND_TYPE_TIMELINE) {

            $timeline = $this->timelineData;

            if(!$timeline) {
                $this->errorMessage = "Invalid event selection";
                return;
            }

            RegistrationAttendance::create([
                'timeline_id' => $timeline->id,
                'registration_id' => $registration->id,
            ]);
        } else {
            
            $session = $this->sessionData;

            if(!$session) {
                $this->errorMessage = "Invalid event selection";
                return;
            }

            RegistrationAttendance::create([
                'session_id' => $session->id,
                'registration_id' => $registration->id,
            ]);
        }

        $this->cancel();
    }

    protected function getViewData(): array
    {
        $isLogged = auth()->check();

        $currentScheduledConference = app()->getCurrentScheduledConference();

        $timelines = Timeline::where('hide', false)
            ->orderBy('date', 'ASC')
            ->get();

        $userRegistration = !$isLogged ? null : Registration::withTrashed()
            ->where('user_id', auth()->user()->id)
            ->first();

        $isParticipant = $userRegistration && ($userRegistration->registrationPayment->state === RegistrationPaymentState::Paid->value) ;

        $typeData = $this->typeData ?? null;
        $timelineData = $this->timelineData ?? null;
        $sessionData = $this->sessionData ?? null;

        return [
            'isLogged' => $isLogged,
            'currentScheduledConference' => $currentScheduledConference,
            'userRegistration' => $userRegistration,
            'isParticipant' => $isParticipant,
            'timelines' => $timelines,
            'typeData' => $typeData,
            'timelineData' => $timelineData,
            'sessionData' => $sessionData,
            'errorMessage' => $this->errorMessage,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Agenda',
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
