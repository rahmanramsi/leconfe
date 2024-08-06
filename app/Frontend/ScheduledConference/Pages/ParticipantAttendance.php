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

    public ?string $typeData = null;

    public ?Timeline $timelineData = null;

    public ?Agenda $agendaData = null;

    public bool $isOpen = false;

    public ?string $errorMessage = null;

    public const ATTEND_TYPE_TIMELINE = 'timeline';
    public const ATTEND_TYPE_AGENDA = 'agenda';

    public function mount()
    {
    }

    public function attend($data_id, $data_type): void
    {
        if($data_type === self::ATTEND_TYPE_TIMELINE) {
            $timeline = Timeline::where('id', $data_id)->first();

            if(!$timeline) return;

            $this->timelineData = $timeline;
        } else {
            $agenda = Agenda::where('id', $data_id)->first();

            if(!$agenda) return;

            $this->agendaData = $agenda;
        }
        $this->typeData = $data_type;
        $this->isOpen = true;
    }

    public function cancel(): void
    {
        $this->typeData = null;
        $this->timelineData = null;
        $this->agendaData = null;
        $this->errorMessage = false;
        $this->isOpen = false;
    }

    public function confirm(): void
    {
        // belum login
        if(!auth()->check()) {
            $this->errorMessage = "You're not logged in";
            return;
        }

        $typeData = $this->typeData;
        if($typeData === self::ATTEND_TYPE_TIMELINE) {

            $timeline = $this->timelineData;
            
            $registration = Registration::withTrashed()
                ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                ->whereUserId(auth()->user()->id)
                ->first();

            // timeline yang dipilih tidak valid
            if(!$timeline) {
                $this->errorMessage = "Invalid event selection";
                return;
            }
            // tidak melakukan registrasi
            if(!$registration) {
                $this->errorMessage = "You're not registrant of " . app()->getCurrentScheduledConference()->title;
                return;
            }
            // belum melakukan pembayaran
            if($registration->registrationPayment->state !== RegistrationPaymentState::Paid->value) { 
                $this->errorMessage = "You're not participant of " . app()->getCurrentScheduledConference()->title;
                return;
            }

            RegistrationAttendance::create([
                'timeline_id' => $timeline->id,
                'registration_id' => $registration->id,
            ]);
        } else {
            
            $agenda = $this->agendaData;
            
            $registration = Registration::withTrashed()
                ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
                ->whereUserId(auth()->user()->id)
                ->first();

            // agenda yang dipilih tidak valid
            if(!$agenda) {
                $this->errorMessage = "Invalid event selection";
                return;
            }
            // tidak melakukan registrasi
            if(!$registration) {
                $this->errorMessage = "You're not registrant of " . app()->getCurrentScheduledConference()->title;
                return;
            }
            // belum melakukan pembayaran
            if($registration->registrationPayment->state !== RegistrationPaymentState::Paid->value) { 
                $this->errorMessage = "You're not participant of " . app()->getCurrentScheduledConference()->title;
                return;
            }

            RegistrationAttendance::create([
                'agenda_id' => $agenda->id,
                'registration_id' => $registration->id,
            ]);
        }

        $this->cancel();
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $timelines = Timeline::where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->orderBy('date', 'ASC')
            ->get();

        $typeData = $this->typeData ?? null;
        $timelineData = $this->timelineData ?? null;
        $agendaData = $this->agendaData ?? null;

        return [
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'timelines' => $timelines,
            // data confirmation
            'typeData' => $typeData,
            'timelineData' => $timelineData,
            'agendaData' => $agendaData,
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
