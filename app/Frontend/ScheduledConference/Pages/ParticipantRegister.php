<?php

namespace App\Frontend\ScheduledConference\Pages;

use App\Models\Enums\RegistrationPaymentState;
use Squire\Models\Country;
use App\Models\Registration;
use App\Models\Enums\UserRole;
use Livewire\Attributes\Title;
use App\Models\RegistrationType;
use App\Models\Timeline;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Rahmanramsi\LivewirePageGroup\PageGroup;
use Rahmanramsi\LivewirePageGroup\Pages\Page;

class ParticipantRegister extends Page
{
    protected static string $view = 'frontend.scheduledConference.pages.participant-register';

    protected static ?string $slug = 'participant-registration';

    public bool $isSubmit = false;

    public array $formData;

    public $type;

    public function mount()
    {
        $isLogged = auth()->check();
        $userRegistration = !$isLogged ? null : Registration::withTrashed()
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($userRegistration)
            return redirect(route(ParticipantRegisterStatus::getRouteName()));
    }

    public function rules(): array
    {
        $rules =  [
            'type' => 'required',
        ];
        return $rules;
    }

    public function messages(): array
    {
        $message = [
            'type' => 'Registration type have to selected.',
        ];
        return $message;
    }

    public function register()
    {
        if (!auth()->check()) return;

        $data = $this->validate();
        $registrationType = RegistrationType::where('id', $data['type'])->first();
        $registrationTypeList = RegistrationType::select('*')
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->get();

        if ($registrationTypeList->isEmpty()) return;
        if (!$registrationType) return;
        if ($registrationType->isExpired()) return;
        if ($registrationType->getQuotaLeft() <= 0) return;

        $this->formData = Arr::only($data, 'type');
        $this->isSubmit = true;

        return 1;
    }

    public function confirm()
    {
        if (!auth()->check()) return;

        $data = $this->formData;
        $registrationType = RegistrationType::where('id', $data['type'])->first();
        $isFree = $registrationType->currency === 'free';

        $registration = Registration::create([
            'user_id' => auth()->user()->id,
            'registration_type_id' => $data['type'],
        ]);

        $registration->registrationPayment()->create([
            'name' => $registrationType->type,
            'description' => $registrationType->getMeta('description'),
            'cost' => $registrationType->cost,
            'currency' => $registrationType->currency,
            'state' => $isFree ? RegistrationPaymentState::Paid : RegistrationPaymentState::Unpaid,
            'paid_at' => $isFree ? now() : null,
        ]);

        return redirect(request()->header('Referer'));
    }

    public function cancel()
    {
        $this->formData = [];
        $this->isSubmit = false;

        return 1;
    }

    protected function getViewData(): array
    {
        $currentScheduledConference = app()->getCurrentScheduledConference();

        $isLogged = auth()->check();

        $userRegistration = !$isLogged ? null : Registration::withTrashed()
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->where('user_id', auth()->user()->id)
            ->first();

        $userModel = !$isLogged ? null : auth()->user();

        $userCountry = !$isLogged ? null : Country::find(auth()->user()->getMeta('country', null));

        $registrationTypeList = RegistrationType::select('*')
            ->where('scheduled_conference_id', app()->getCurrentScheduledConferenceId())
            ->get();

        $registrationType = null;
        if (isset($this->formData['type'])) {
            $registrationType = RegistrationType::where('id', $this->formData['type'])->first();
        }

        return [
            // participant registration
            'currentScheduledConference' => $currentScheduledConference,
            'isLogged' => $isLogged,
            'userModel' => $userModel,
            'userCountry' => $userCountry,
            'userRegistration' => $userRegistration,
            'registrationTypeList' => $registrationTypeList,
            'registrationOpen' => Timeline::isRegistrationOpen(),
            // participant registration confirm
            'isSubmit' => $this->isSubmit,
            'registrationType' => $registrationType,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route(Home::getRouteName()) => 'Home',
            'Audience Registration',
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
